<?php

namespace App\Service\NotificationMail;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\TemplateTag;
use App\Service\Util\GenderConverter;

class NotificationMailer
{


    public function __construct(
        private readonly EmailTemplateRepository $emailTemplateRepository,
        private readonly EmailGenerator          $emailGenerator,
        private readonly GenderConverter         $genderConverter,
        private readonly EmailServiceInterface   $emailService,
    )
    {
    }


    public function prepareAndSendALLNotifications(array $notfications)
    {

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
            $emailData = $this->emailGenerator->generateFromTemplate($emailTemplate, $this->replaceParams($content, $user));
            $emailData->setTo($emailDest)->setReplyTo($structure->getReplyTo());
            $this->emailService->sendBatch([$emailData]);
        }
    }


    public function replaceParams(array $content, User $user): array
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