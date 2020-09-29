<?php


namespace App\Service\Email;

use App\Entity\User;

interface IEmail
{
    public function send(string $subject, array $to, string $templatePath, array $variables);
    public function sendLinkToRecipient(iterable $statuses);
    public function sendReinitPassword(User $user, string $token);
}
