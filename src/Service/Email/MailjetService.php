<?php

namespace App\Service\Email;

use App\Entity\User;
use App\Service\EmailTemplate\HtmlTag;
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
    public function sendBatch(array $emails): void
    {
        $messages = $this->generateMailjetMessages($emails);
        $response = $this->mailjetClient->post(Resources::$Email, ['body' => ['Messages' => $messages]]);

        if (!$response->success()) {
            $this->logger->error('mailetError : ' . $response->getReasonPhrase());

            throw new EmailNotSendException($response->getReasonPhrase(), $response->getStatus());
        }
    }

    /**
     * @param EmailData[] $emails
     */
    private function generateMailjetMessages(array $emails): array
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
            ];

            if (EmailData::FORMAT_HTML === $email->getFormat()) {
                $message['HTMLPart'] = HtmlTag::START_HTML . $email->getContent() . HtmlTag::END_HTML;
            }

            if (EmailData::FORMAT_TEXT === $email->getFormat()) {
                $message['TextPart'] = $email->getContent();
            }

            if ($email->getReplyTo()) {
                $message['ReplyTo'] = [
                    'Email' => $email->getReplyTo(),
                    'Name' => 'Repondre',
                ];
            }

            $this->setAttachment($message, $email);

            $messages[] = $message;
        }

        return $messages;
    }

    private function setAttachment(array &$message, EmailData $email)
    {
        if (!$email->isAttachment() || !file_exists($email->getAttachment()->getPath())) {
            return;
        }

        if (filesize($email->getAttachment()->getPath()) > $this->bag->get('max_email_attachment_file_size')) {
            return;
        }

        $message['Attachments'] = [
            [
                'ContentType' => $email->getAttachment()->getContentType(),
                'Filename' => $email->getAttachment()->getFileName(),
                'Base64Content' => base64_encode(file_get_contents($email->getAttachment()->getPath())),
            ],
        ];
    }

    public function sendReInitPassword(User $user, string $token): void
    {
        // TODO: Implement sendReInitPassword() method.
    }
}
