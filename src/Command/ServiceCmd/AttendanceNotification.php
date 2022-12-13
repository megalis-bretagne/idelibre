<?php

namespace App\Command\ServiceCmd;

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

class AttendanceNotification
{

    public function __construct(
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
        return $this->sittingRepository->findActiveSittingsAfterDate($structure, new DateTime('- 4month'));
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
        $attendanceDatas = $this->prepareDatas($structure);
        if( !empty($attendanceDatas) ){
            $this->prepareAndSendMail($structure, $attendanceDatas, $users);
        }
    }

    public function prepareDatas(Structure $structure ): array
    {
        $attendanceDatas = [];
        $sittings = $this->listActiveSittingsByStructure($structure);
        foreach($sittings as $sitting) {
            $seanceName = 'Séance ' . $sitting->getName(). ' du '.$sitting->getDate()->format('d/m/Y à H:i');
            $statut =  'Non renseigné';
            $convocations = $this->convocationRepository->getConvocationsWithUserBySitting($sitting);
            foreach( $convocations as $convocation ) {
                if (!empty($convocation->getAttendance())) {
                    $statut = ucfirst($convocation->getAttendance() );
                    if (!empty($convocation->getDeputy())) {
                        $statut = ucfirst($convocation->getAttendance()) . ' - Mandataire: ' . $convocation->getDeputy();
                    }
                }
                $attendanceDatas[$seanceName][]  = $convocation->getUser()->getFirstName() . ' ' . $convocation->getUser()->getLastName() . ' - Statut : ' .$statut;
            }
        }
        return $attendanceDatas;
    }

    private function prepareAndSendMail(Structure $structure, array $content, array $users): void
    {
        $emailsData = [];
        $emailTemplate = $this->emailTemplateRepository->findOneByStructureAndCategory($structure, 'procuration');
        if (!empty($emailTemplate)) {
            foreach( $users as $user ) {
                $emailDest = $user->getEmail();
                $emailData = $this->emailGenerator->generateFromTemplate($emailTemplate, $this->generateParams($content, $user));
                $emailData->setTo($emailDest)->setReplyTo($structure->getReplyTo());
                $emailsData[] = $emailData;
            }
        }
        $this->emailService->sendBatch($emailsData);
    }

    public function generateParams(array $content, User $user): array
    {
        $allDatas = [];
        foreach( $content  as $seanceName => $contentData ) {
            $datas = $seanceName."\n".'- ';
            $datas .= implode("\n".'- ', $contentData);
            $allDatas[] = $datas."\n";
        }
        $contentToDisplay = nl2br(implode("\n", $allDatas));
        return [
            TemplateTag::SITTING_PROCURATION => $contentToDisplay,
            TemplateTag::ACTOR_FIRST_NAME => $user->getFirstName(),
            TemplateTag::ACTOR_LAST_NAME => $user->getLastName(),
            TemplateTag::ACTOR_USERNAME => $user->getUsername(),
            TemplateTag::ACTOR_TITLE => $user->getTitle() ?? '',
            TemplateTag::ACTOR_GENDER => $this->genderConverter->format($user->getGender()),
        ];
    }
}
