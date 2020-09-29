<?php


namespace App\Service\Email;

use App\Entity\User;

class EmailFake implements IEmail
{
    public function send(string $subject, array $to, string $templatePath, array $variables)
    {
        // TODO: Implement send() method.
    }

    public function sendLinkToRecipient(iterable $statuses)
    {
        // TODO: Implement sendLinkToRecipient() method.
    }

    public function sendReinitPassword(User $user, string $token)
    {
        // TODO: Implement sendReinitPassword() method.
    }
}
{

}
