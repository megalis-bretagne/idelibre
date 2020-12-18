<?php

namespace App\Service\Email;

use App\Entity\EmailTemplate;
use App\Entity\User;

class EmailServiceInterfaceFake implements EmailServiceInterface
{
    public function sendTemplate(string $subject, array $to, EmailTemplate $emailTemplate, array $variables): void
    {
    }

    public function sendReInitPassword(User $user, string $token): void
    {
    }

    /**
     * @param EmailData[] $emails
     */
    public function sendBatch(array $emails): void {
    }
}
