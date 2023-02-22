<?php

namespace App\Service\Email;

use App\Entity\User;

class EmailServiceInterfaceFake implements EmailServiceInterface
{
    /**
     * @param EmailData[] $emailsData
     */
    public function sendBatch(array $emailsData): void
    {
        // interface
    }

    public function sendInitPassword(User $user, string $token): void
    {
        // interface
    }

    public function sendResetPassword(User $user, string $token): void
    {
        // interface
    }

    public function sendReloadPassword(User $user, string $token): void
    {
        // interface
    }
}
