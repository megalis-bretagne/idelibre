<?php

namespace App\Command\ServiceCmd;

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
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\TemplateTag;
use App\Service\Util\GenderConverter;
use DateTime;
use Twig\Environment;

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
     * @return Structure[]
     */
    private function listStructures(): array
    {
        return $this->structureRepository->findAll();
    }

    /**
     * @return Sitting[]
     */
    private function listActiveSittingsByStructure(Structure $structure): array
    {
        return $this->sittingRepository->findActiveSittingsAfterDate($structure, new DateTime('0 days'));
    }

    public function genAllAttendanceNotification(): void
    {
        foreach ($this->listStructures() as $structure) {
            $this->getAttendanceNotification($structure);
        }
    }

    public function getAttendanceNotification(Structure $structure): void
    {

        $users = $this->userRepository->findSecretariesAndAdminByStructure($structure)->getQuery()->getResult();
        $sittings = $this->listActiveSittingsByStructure($structure);
        foreach( $users as $user) {
            if( $user->getAcceptMailRecap() ) {
                $attendanceDatas = [];
                foreach( $sittings as $sitting ) {
                    if( $this->isAuthorizedSittingType($sitting->getType(), $user) ) {
                        $attendanceDatas[] = $this->prepareDatas($sitting);
                    }
                }
                $this->prepareAndSendMail($structure, $attendanceDatas, $user);
            }
        }
    }

    public function prepareDatas(Sitting $sitting): string
    {
        $convocations = $this->convocationRepository->getConvocationsWithUserBySitting($sitting);
        return $this->twig->render('generate/mailing_recap_template.html.twig', [
            'convocations' => $convocations,
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);
    }

    /**
     * @param array<string> $content
     */
    private function prepareAndSendMail(Structure $structure, array $content, User $user): void
    {
        if( empty($content ) ){
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
        if( $user->getRole()->getName() == 'Admin') {
            return true;
        }
        return $user->getAuthorizedTypes()->contains($type);
    }
}
