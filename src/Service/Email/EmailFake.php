<?php


namespace App\Service\Email;

use App\Entity\User;

class EmailFake implements IEmail
{
    public function send(string $subject, array $to, string $templatePath, array $variables): void
    {
    }

    public function sendLinkToRecipient(iterable $statuses): void
    {
    }

    public function sendReinitPassword(User $user, string $token): void
    {
    }
}
