<?php

namespace App\Service\Email;

use App\Entity\User;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SimpleEmailService implements EmailServiceInterface
{
    private Swift_Mailer $mailer;
    private ParameterBagInterface $bag;

    public function __construct(Swift_Mailer $mailer, ParameterBagInterface $bag)
    {
        $this->mailer = $mailer;
        $this->bag = $bag;
    }

    /**
     * @param EmailData[] $emails
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emails): void
    {
        foreach ($emails as $email) {
            try {
                $message = (new Swift_Message($email->getSubject()))
                    ->setFrom($this->bag->get('email_from'))
                    ->setTo($email->getTo())
                    ->setBody(
                        $email->getContent(),
                        'text/html'
                    );

                if ($email->getReplyTo()) {
                    $message->setReplyTo($email->getReplyTo());
                }
            } catch (Exception $e) {
                throw new EmailNotSendException($e->getMessage());
            }

            $this->mailer->send($message);
        }
    }

    public function sendReInitPassword(User $user, string $token): void
    {
        // TODO: Implement sendReInitPassword() method.
    }
}