<?php

namespace App\Service\Email;

use App\Entity\User;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\HtmlTag;
use Mailjet\Client;
use Mailjet\Resources;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailjetService implements EmailServiceInterface
{
    public function __construct(
        private readonly Client $mailjetClient,
        private readonly ParameterBagInterface $bag,
        private readonly LoggerInterface $logger,
        private readonly EmailGenerator $emailGenerator,
    ) {
    }

    /**
     * @param EmailData[] $emailsData
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emailsData): void
    {
        $messages = $this->generateMailjetMessages($emailsData);
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

    private function setAttachment(array & $message, EmailData $email)
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

    /**
     * @throws EmailNotSendException
     */
    public function sendInitPassword(User $user, string $token): void
    {
        $contentSubject = '[#NOM_PRODUIT#] Initialisation de votre mot de passe';
        $subject = $this->emailGenerator->generateSubject($user, $contentSubject);

        $contents = $this->emailGenerator->generateInitPassword(
            $user,
            $token
        );

        $this->send($user->getEmail(), $subject, $contents['html'], $contents['text']);
    }

    /**
     * @throws EmailNotSendException
     */
    public function sendResetPassword(User $user, string $token): void
    {
        $contentSubject = '[#NOM_PRODUIT#] Réinitialiser votre mot de passe';
        $subject = $this->emailGenerator->generateSubject($user, $contentSubject);

        $contents = $this->emailGenerator->generateForgetPassword(
            $user,
            $token
        );

        $this->send($user->getEmail(), $subject, $contents['html'], $contents['text']);
    }

    /**
     * @throws EmailNotSendException
     */
    private function send(string $userEmail, string $subject, string $contentHtml, string $contentText): void
    {
        $message = [
            [
                'From' => [
                    'Email' => $this->bag->get('email_from'),
                    'Name' => $this->bag->get('email_alias')
                ],
                'To' => [
                    [
                        'Email' => $userEmail,
                    ]
                ],
                'Subject' => $subject,
                'HTMLPart' => $contentHtml,
                'TextPart' => $contentText,
            ]
        ];

        $result = $this->mailjetClient->post(Resources::$Email, [
            'body' => [
                'Messages' => $message
            ]
        ]);

        if (false === $result->success()) {
            throw new EmailNotSendException($result->getBody()['ErrorMessage'], $result->getStatus());
        }
    }
}
