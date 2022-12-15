<?php

namespace App\Command\ServiceCmd;

use App\Entity\EmailTemplate;
use App\Entity\Sitting;
use App\Entity\Structure;
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
use Eluceo\iCal\Domain\ValueObject\Category;
use Twig\Environment;

class AttendanceNotification
{

    public function __construct(
        private Environment $twig,
        private StructureRepository $structureRepository,
        private SittingRepository $sittingRepository,
        private ConvocationRepository $convocationRepository,
        private EmailTemplateRepository $emailTemplateRepository,
        private UserRepository $userRepository,
        private EmailServiceInterface $emailService,
        private EmailGenerator $emailGenerator,
        private GenderConverter $genderConverter,
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

        $users = $this->userRepository->findSecretariesByStructure($structure)->getQuery()->getResult();
        $sittings = $this->listActiveSittingsByStructure($structure);
        foreach( $users as $user) {
            if( $user->getAcceptMailRecap() ) {
                $attendanceDatas = [];
                foreach( $sittings as $sitting ) {
                    if( $user->getAuthorizedTypes()->contains($sitting->getType()) ) {
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
}
