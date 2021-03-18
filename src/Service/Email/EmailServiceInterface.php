<?php

namespace App\Service\Email;

use App\Entity\User;

interface EmailServiceInterface
{
    public function sendReInitPassword(User $user, string $token): void;

    /**
     * @param EmailData[] $emails
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emails, string $format = EmailData::FORMAT_HTML): void;
}

//TODO email format should be in emailData !
