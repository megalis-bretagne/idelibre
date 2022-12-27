<?php

namespace App\Service\Email;

use App\Entity\User;

interface EmailServiceInterface
{
    /**
     * @param EmailData[] $emailsData
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emailsData): void;
    public function sendInitPassword(User $user, string $token): void;
    public function sendResetPassword(User $user, string $token): void;
    public function sendReloadPassword(User $user, string $token): void;
}
