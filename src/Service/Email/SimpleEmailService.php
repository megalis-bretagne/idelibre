<?php

namespace App\Service\Email;

use App\Entity\EmailTemplate;
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
     */
    public function sendBatch(array $emails): void {
        foreach ($emails as $email){
            $message = (new Swift_Message($email->getSubject()))
                ->setFrom($this->bag->get('email_from'))
                ->setTo($email->getTo())
                ->setBody(
                    $email->getContent(),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }


    public function sendReInitPassword(User $user, string $token): void
    {
        // TODO: Implement sendReInitPassword() method.
    }

    public function sendTemplate(string $subject, array $to, EmailTemplate $emailTemplate, array $variables): void
    {
        // TODO: Implement sendTemplate() method.
    }
}
