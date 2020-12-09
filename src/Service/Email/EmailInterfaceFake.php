<?php

namespace App\Service\Email;

use App\Entity\User;

class EmailInterfaceFake implements EmailInterface
{
    public function sendTemplate(string $subject, array $to, string $templatePath, array $variables): void
    {
    }

    public function sendLinkToRecipient(iterable $statuses): void
    {
    }

    public function sendReInitPassword(User $user, string $token): void
    {
    }
}
