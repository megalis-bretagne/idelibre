<?php

namespace App\Service\Email;

use App\Entity\Sitting;

class NotificationService
{
    public function __construct(private EmailServiceInterface $emailService)
    {
    }

    private function generateEmailDataList(Sitting $sitting, string $subject, string $content): array
    {
        $emails = [];
        foreach ($sitting->getConvocations() as $convocation) {
            if( $convocation->getUser()->getIsActive() ) {
                $emailData = new EmailData($subject, $content, EmailData::FORMAT_TEXT);
                $emailData->setTo($convocation->getUser()->getEmail());
                $emailData->setReplyTo($sitting->getStructure()->getReplyTo());
                $emails[] = $emailData;
            }
        }

        return $emails;
    }

    public function reNotify(Sitting $sitting, string $subject, string $content)
    {
        $emailDataList = $this->generateEmailDataList($sitting, $subject, $content);
        $this->emailService->sendBatch($emailDataList, EmailData::FORMAT_TEXT);
    }
}
