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
    }

    public function sendInitPassword(User $user, string $token)
    {
    }

    public function sendResetPassword(User $user, string $token)
    {
    }
}
