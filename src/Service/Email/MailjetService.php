<?php

namespace App\Service\Email;

use App\Entity\User;
use Mailjet\Client;
use Mailjet\Resources;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailjetService implements EmailServiceInterface
{
    private Client $mailjetClient;
    private ParameterBagInterface $bag;
    private LoggerInterface $logger;

    public function __construct(Client $mailjetClient, ParameterBagInterface $bag, LoggerInterface $logger)
    {
        $this->mailjetClient = $mailjetClient;
        $this->bag = $bag;
        $this->logger = $logger;
    }

    /**
     * @param EmailData[] $emails
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emails, string $type = EmailData::TYPE_HTML): void
    {
        $messages = $this->generateMailjetMessages($emails, $type);
        $response = $this->mailjetClient->post(Resources::$Email, ['body' => ['Messages' => $messages]]);

        if (!$response->success()) {
            $this->logger->error('mailetError : ' . $response->getReasonPhrase());

            throw new EmailNotSendException($response->getReasonPhrase(), $response->getStatus());
        }
    }

    /**
     * @param EmailData[] $emails
     */
    private function generateMailjetMessages(array $emails, string $type): array
    {
        $messages = [];
        foreach ($emails as $email) {
            $message = [
                'From' => [
                    'Email' => $this->bag->get('email_from'),
                    'Name' => $this->bag->get('email_alias'),
                ],
                'To' => [
                    [
                        'Email' => $email->getTo(),
                    ],
                ],
                'Subject' => $email->getSubject(),
                'HTMLPart' => $email->getContent(),
            ];

            if (EmailData::TYPE_HTML === $type) {
                $message['HTMLPart'] = $email->getContent();
            }

            if (EmailData::TYPE_TEXT === $type) {
                $message['TextPart'] = $email->getContent();
            }

            if ($email->getReplyTo()) {
                $message['ReplyTo'] = [
                    'Email' => $email->getReplyTo(),
                    'Name' => 'Repondre',
                ];
            }
            $messages[] = $message;
        }

        return $messages;
    }

    public function sendReInitPassword(User $user, string $token): void
    {
        // TODO: Implement sendReInitPassword() method.
    }
}
