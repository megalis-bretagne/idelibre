<?php

namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\Role;
use App\Entity\Sitting;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\Timestamp\TimestampManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConvocationManager
{
    private EntityManagerInterface $em;
    private ConvocationRepository $convocationRepository;
    private TimestampManager $timestampManager;
    private LoggerInterface $logger;
    private ParameterBagInterface $bag;
    private EmailServiceInterface $emailService;
    private EmailGenerator $emailGenerator;

    public function __construct(
        EntityManagerInterface $em,
        ConvocationRepository $convocationRepository,
        TimestampManager $timestampManager,
        LoggerInterface $logger,
        ParameterBagInterface $bag,
        EmailServiceInterface $emailService,
        EmailGenerator $emailGenerator
    ) {
        $this->em = $em;
        $this->convocationRepository = $convocationRepository;
        $this->timestampManager = $timestampManager;
        $this->logger = $logger;
        $this->bag = $bag;
        $this->emailService = $emailService;
        $this->emailGenerator = $emailGenerator;
    }

    public function createConvocations(Sitting $sitting): void
    {
        $associatedUsers = $sitting->getType()->getAssociatedUsers();
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
     */
    public function sendAllConvocations(Sitting $sitting, ?string $userProfile): void
    {
        $convocations = $this->getConvocationByUserProfile($sitting, $userProfile);
        while (count($convocations)) {
            $convocationBatch = array_splice($convocations, 0, $this->bag->get('max_batch_email'));
            $this->timestampAndActiveConvocations($sitting, $convocationBatch);

            $emails = $this->generateEmailsData($sitting, $convocationBatch);
            $this->emailService->sendBatch($emails);
        }
    }

    public function sendConvocation(Convocation $convocation)
    {
        $this->timestampAndActiveConvocations($convocation->getSitting(), [$convocation]);

        // TODO send email !
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
     * @param Convocation[] $convocations
     */
    public function deleteConvocations(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $this->em->remove($convocation);
            $this->deleteAssociatedTimestamp($convocation);
        }
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
        }

        return $emails;
    }

    private function getConvocationByUserProfile(Sitting $sitting, ?string $userProfile): array
    {
        if (!$userProfile) {
            return $sitting->getConvocations()->toArray();
        }

        switch ($userProfile) {
            case Role::NAME_ROLE_ACTOR:
                return $this->convocationRepository->getActorConvocationsBySitting($sitting);
            case Role::NAME_ROLE_GUEST:
                return $this->convocationRepository->getGuestConvocationsBySitting($sitting);
            default: return  $this->convocationRepository->getInvitableEmployeeConvocationsBySitting($sitting);
        }
    }
}
