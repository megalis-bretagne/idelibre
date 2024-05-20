<?php

namespace App\Command\ServiceCmd;

use App\Entity\Convocation;
use App\Entity\EmailTemplate;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Repository\EmailTemplateRepository;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\TemplateTag;
use App\Service\Util\GenderConverter;
use DateTime;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AttendanceNotification
{
    public function __construct(
        private readonly Environment $twig,
        private readonly StructureRepository $structureRepository,
        private readonly SittingRepository $sittingRepository,
        private readonly ConvocationRepository $convocationRepository,
        private readonly EmailTemplateRepository $emailTemplateRepository,
        private readonly UserRepository $userRepository,
        private readonly EmailServiceInterface $emailService,
        private readonly EmailGenerator $emailGenerator,
        private readonly GenderConverter $genderConverter,
    ) {
    }


    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws EmailNotSendException
     */
    public function genAllAttendanceNotification(): void
    {
        $structures = $this->structureRepository->findBy(['isActive' => true]);
        foreach ($structures as $structure) {
            $this->sendNotifications($structure);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws EmailNotSendException
     */
    public function sendNotifications(Structure $structure): void
    {
        $users = $this->userRepository->findSecretariesAndAdminByStructureWithMailsRecap($structure)->getQuery()->getResult();
        $sittings = $this->sittingRepository->findActiveSittingsAfterDateByStructure($structure, new DateTime('0 days'));

        foreach ($users as $user) {
            $this->hydrateAndSendSittingDatas($sittings, $user, $structure);
        }
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws EmailNotSendException
     */
    private function hydrateAndSendSittingDatas(array $sittings, User $user, Structure $structure): void
    {
        $attendanceData = [];
        foreach ($sittings as $sitting) {
            if ($this->isAuthorizedSittingType($sitting->getType(), $user)) {
                $attendanceData[] = $this->prepareDatas($sitting);
            }
        }
        if (empty($attendanceData)) {
            return;
        }

        $this->prepareAndSendMail($structure, $attendanceData, $user);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function prepareDatas(Sitting $sitting): string
    {
        $convocations = $this->convocationRepository->getConvocationsWithUserBySitting($sitting);

        return $this->twig->render('generate/mailing_recap_template.html.twig', [
            'convocations' => $convocations,
            'attendance' => [
                Convocation::PRESENT => 'Présent',
                Convocation::ABSENT => 'Absent',
                Convocation::REMOTE => 'Distanciel',
                Convocation::ABSENT_GIVE_POA => 'Donne pouvoir par procuration',
                Convocation::ABSENT_SEND_DEPUTY => 'Remplacé par son suppléant',
            ],
            'category' => [
                Convocation::CATEGORY_CONVOCATION => 'Élus',
                Convocation::CATEGORY_INVITATION => 'Invités/Personnels administratifs',
            ],
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);
    }

    /**
     * @param array<string> $content
     * @throws EmailNotSendException
     */
    private function prepareAndSendMail(Structure $structure, array $content, User $user): void
    {
        if (empty($content)) {
            return;
        }

        $emailTemplate = $this->emailTemplateRepository->findOneByStructureAndCategory($structure, EmailTemplate::CATEGORY_RECAPITULATIF);
        if (!empty($emailTemplate)) {
            $emailDest = $user->getEmail();
            $emailData = $this->emailGenerator->generateFromTemplate($emailTemplate, $this->generateParams($content, $user));
            $emailData->setTo($emailDest)->setReplyTo($structure->getReplyTo());
            $this->emailService->sendBatch([$emailData]);
        }
    }

    public function generateParams(array $content, User $user): array
    {
        $contentToDisplay = implode("\n", $content);

        return [
            TemplateTag::SITTING_RECAPITULATIF => $contentToDisplay,
            TemplateTag::ACTOR_FIRST_NAME => $user->getFirstName(),
            TemplateTag::ACTOR_LAST_NAME => $user->getLastName(),
            TemplateTag::ACTOR_USERNAME => $user->getUsername(),
            TemplateTag::ACTOR_TITLE => $user->getTitle() ?? '',
            TemplateTag::ACTOR_GENDER => $this->genderConverter->format($user->getGender()),
        ];
    }

    private function isAuthorizedSittingType(Type $type, User $user): bool
    {
        return $user->getRole()->getName() === "Admin" or $user->getAuthorizedTypes()->contains($type);
    }
}
