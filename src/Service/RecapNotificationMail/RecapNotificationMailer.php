<?php

namespace App\Service\RecapNotificationMail;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\EmailServiceInterface;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\TemplateTag;
use App\Service\RecapNotificationMail\Model\EmailRecapData;
use App\Service\Util\GenderConverter;
use Psr\Log\LoggerInterface;

class RecapNotificationMailer
{
    public function __construct(
        private readonly EmailTemplateRepository $emailTemplateRepository,
        private readonly EmailGenerator          $emailGenerator,
        private readonly GenderConverter         $genderConverter,
        private readonly EmailServiceInterface   $emailService,
        private readonly LoggerInterface         $logger
    ) {
    }


    /**
     * @param array<EmailRecapData> $notificationsToSend
     */
    public function sendAllNotifications(array $notificationsToSend)
    {
        foreach ($notificationsToSend as $notificationToSend) {
            $this->prepareAndSendMail($notificationToSend->getStructure(), $notificationToSend->getGeneratedRecapContents(), $notificationToSend->getUser());
        }
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

        if (empty($emailTemplate)) {
            $this->logger->error('No email template found for structure ' . $structure->getId() . ' and category ' . EmailTemplate::CATEGORY_RECAPITULATIF);
            return;
        }

        $emailDest = $user->getEmail();
        $emailData = $this->emailGenerator->generateFromTemplate($emailTemplate, $this->getResolveParams($content, $user));
        $emailData->setTo($emailDest)->setReplyTo($structure->getReplyTo());
        $this->emailService->sendBatch([$emailData]);
    }


    public function getResolveParams(array $content, User $user): array
    {
        return [
            TemplateTag::SITTING_RECAPITULATIF => implode("\n", $content),
            TemplateTag::ACTOR_FIRST_NAME => $user->getFirstName(),
            TemplateTag::ACTOR_LAST_NAME => $user->getLastName(),
            TemplateTag::ACTOR_USERNAME => $user->getUsername(),
            TemplateTag::ACTOR_TITLE => $user->getTitle() ?? '',
            TemplateTag::ACTOR_GENDER => $this->genderConverter->format($user->getGender()),
        ];
    }
}
