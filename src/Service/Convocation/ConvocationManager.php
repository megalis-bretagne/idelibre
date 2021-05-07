<?php

namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\File;
use App\Entity\Role;
use App\Entity\Sitting;
use App\Entity\User;
use App\Message\ConvocationSent;
use App\Repository\ConvocationRepository;
use App\Repository\UserRepository;
use App\Service\ClientNotifier\ClientNotifierInterface;
use App\Service\Email\Attachment;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\Timestamp\TimestampManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConvocationManager
{
    private EntityManagerInterface $em;
    private ConvocationRepository $convocationRepository;
    private TimestampManager $timestampManager;
    private LoggerInterface $logger;
    private ParameterBagInterface $bag;
    private EmailServiceInterface $emailService;
    private EmailGenerator $emailGenerator;
    private UserRepository $userRepository;
    private ClientNotifierInterface $clientNotifier;

    public function __construct(
        EntityManagerInterface $em,
        ConvocationRepository $convocationRepository,
        TimestampManager $timestampManager,
        LoggerInterface $logger,
        ParameterBagInterface $bag,
        EmailServiceInterface $emailService,
        EmailGenerator $emailGenerator,
        UserRepository $userRepository,
        ClientNotifierInterface $clientNotifier
    ) {
        $this->em = $em;
        $this->convocationRepository = $convocationRepository;
        $this->timestampManager = $timestampManager;
        $this->logger = $logger;
        $this->bag = $bag;
        $this->emailService = $emailService;
        $this->emailGenerator = $emailGenerator;
        $this->userRepository = $userRepository;
        $this->clientNotifier = $clientNotifier;
    }

    public function createConvocationsActors(Sitting $sitting): void
    {
        $associatedActors = $this->userRepository->getAssociatedActorsWithType($sitting->getType());
        $this->createConvocations($sitting, $associatedActors);
    }

    public function createConvocationsInvitableEmployees(Sitting $sitting): void
    {
        $associatedInvitableEmployees = $this->userRepository->getAssociatedInvitableEmployeesWithType($sitting->getType());
        $this->createConvocations($sitting, $associatedInvitableEmployees);
    }

    public function createConvocationsGuests(Sitting $sitting): void
    {
        $associatedGuests = $this->userRepository->getAssociatedGuestWithType($sitting->getType());
        $this->createConvocations($sitting, $associatedGuests);
    }

    /**
     * @param User[] $associatedUsers
     */
    private function createConvocations(Sitting $sitting, array $associatedUsers): void
    {
        foreach ($associatedUsers as $user) {
            $convocation = new Convocation();
            $convocation->setSitting($sitting)
                ->setUser($user)
                ->setCategory($this->getConvocationCategory($user));
            $this->em->persist($convocation);
        }
    }

    private function getConvocationCategory(User $user): string
    {
        if (Role::NAME_ROLE_ACTOR === $user->getRole()->getName()) {
            return Convocation::CATEGORY_CONVOCATION;
        }

        return Convocation::CATEGORY_INVITATION;
    }

    /**
     * @param User[] $users
     */
    public function addConvocations(iterable $users, Sitting $sitting): void
    {
        foreach ($users as $user) {
            if ($this->alreadyHasConvocation($user, $sitting)) {
                continue;
            }
            $convocation = new Convocation();
            $convocation->setSitting($sitting)
                ->setUser($user)
                ->setCategory($this->getConvocationCategory($user));
            $this->em->persist($convocation);
        }
        $this->em->flush();
    }

    /**
     * @param Convocation[] $convocations
     */
    public function deleteConvocationsNotSent(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            if ($this->isAlreadySent($convocation)) {
                continue;
            }
            $this->em->remove($convocation);
        }
        $this->em->flush();
    }

    private function alreadyHasConvocation(User $user, Sitting $sitting): bool
    {
        $convocation = $this->convocationRepository->findOneBy(['user' => $user, 'sitting' => $sitting]);

        return !empty($convocation);
    }

    /**
     * NB le processe d'envoi et d'hrodatage pourrait (devrait) ce faire en async.
     *
     * @throws ConnectionException
     * @throws EmailNotSendException
     */
    public function sendAllConvocations(Sitting $sitting, ?string $userProfile): void
    {
        $convocations = $this->getConvocationByUserProfile($sitting, $userProfile);
        while (count($convocations)) {
            $convocationBatch = array_splice($convocations, 0, $this->bag->get('max_batch_email'));
            $this->timestampAndActiveConvocations($sitting, $convocationBatch);
            $this->clientNotifier->newSittingNotification($convocationBatch);
            $emails = $this->generateEmailsData($sitting, $convocationBatch);
            $this->emailService->sendBatch($emails);
            //l'envoi des sms en async semble une bien meilleur solution. Preparont deja le terrain

        }
    }

    /**
     * @throws ConnectionException
     * @throws EmailNotSendException
     */
    public function sendConvocation(Convocation $convocation)
    {
        $this->timestampAndActiveConvocations($convocation->getSitting(), [$convocation]);
        $emails = $this->generateEmailsData($convocation->getSitting(), [$convocation]);
        $this->clientNotifier->newSittingNotification([$convocation]);
        $this->emailService->sendBatch($emails);
        $this->messageBus->dispatch(new ConvocationSent($sitting->getId()));
    }

    /**
     * @throws ConnectionException
     */
    private function timestampAndActiveConvocations(Sitting $sitting, iterable $convocations): bool
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $notSentConvocations = $this->filterNotSentConvocations($convocations);
            $timeStamp = $this->timestampManager->createTimestamp($sitting, $notSentConvocations);

            foreach ($notSentConvocations as $convocation) {
                $convocation->setIsActive(true)
                    ->setSentTimestamp($timeStamp);

                $this->em->persist($convocation);
            }
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    private function filterNotSentConvocations(iterable $convocations): array
    {
        $notSent = [];
        foreach ($convocations as $convocation) {
            if (!$this->isAlreadySent($convocation)) {
                $notSent[] = $convocation;
            }
        }

        return $notSent;
    }

    private function isAlreadySent(Convocation $convocation): bool
    {
        return (bool) $convocation->getSentTimestamp();
    }

    /**
     * @param iterable<Convocation> $convocations
     */
    public function deleteConvocations(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $this->em->remove($convocation);
            $this->deleteAssociatedTimestamp($convocation);
        }

        $this->clientNotifier->removedSittingNotification($convocations->toArray());
    }

    private function deleteAssociatedTimestamp(Convocation $convocation): void
    {
        if ($convocation->getSentTimestamp()) {
            $this->timestampManager->delete($convocation->getSentTimestamp());
        }
        if ($convocation->getReceivedTimestamp()) {
            $this->timestampManager->delete($convocation->getReceivedTimestamp());
        }
    }

    /**
     * @param Convocation[] $convocations
     */
    public function deactivate(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $convocation->setIsActive(false);
            $this->em->persist($convocation);
        }
        $this->clientNotifier->removedSittingNotification($convocations->toArray());
    }

    /**
     * @param Convocation[] $convocations
     */
    private function generateEmailsData(Sitting $sitting, array $convocations): array
    {
        $emails = [];
        foreach ($convocations as $convocation) {
            $email = $this->emailGenerator->generateFromTemplateAndConvocation($sitting->getType()->getEmailTemplate(), $convocation);
            $email->setTo($convocation->getUser()->getEmail());
            $email->setReplyTo($sitting->getStructure()->getReplyTo());
            $email->setAttachment($this->getConvocationAttachment($convocation, $sitting));
            $emails[] = $email;
        }

        return $emails;
    }

    private function getConvocationByUserProfile(Sitting $sitting, ?string $userProfile): array
    {
        switch ($userProfile) {
            case Role::NAME_ROLE_ACTOR:
                $convocations = $this->convocationRepository->getActorConvocationsBySitting($sitting);
                break;
            case Role::NAME_ROLE_GUEST:
                $convocations = $this->convocationRepository->getGuestConvocationsBySitting($sitting);
                break;
            case Role::NAME_ROLE_EMPLOYEE:
                $convocations = $this->convocationRepository->getInvitableEmployeeConvocationsBySitting($sitting);
                break;
            default:
                $convocations = $sitting->getConvocations()->toArray();
        }

        return $convocations;
    }

    public function updateConvocationAttendances(array $convocationAttendances)
    {
        foreach ($convocationAttendances as $convocationAttendance) {
            $convocation = $this->convocationRepository->find($convocationAttendance['convocationId']);
            if (!$convocation) {
                //TODO check if you own this convocation !
                throw new NotFoundHttpException("Convocation with id ${convocationAttendance['convocationId']} does not exists");
            }
            $convocation->setAttendance($convocationAttendance['attendance']);
            $convocation->setDeputy($convocationAttendance['deputy']);
        }
        $this->em->flush();
    }

    private function getConvocationAttachment(Convocation $convocation, Sitting $sitting): Attachment
    {
        $file = $this->getConvocationFile($convocation, $sitting);

        return new Attachment($file->getName(), $file->getPath());
    }

    private function getConvocationFile(Convocation $convocation, Sitting $sitting): ?File
    {
        if (Convocation::CATEGORY_CONVOCATION === $convocation->getCategory()) {
            return $sitting->getConvocationFile();
        }

        return $sitting->getInvitationFile();
    }
}
