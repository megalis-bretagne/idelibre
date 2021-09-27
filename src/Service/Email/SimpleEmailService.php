<?php

namespace App\Service\Email;

use App\Service\EmailTemplate\HtmlTag;
use Exception;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SimpleEmailService implements EmailServiceInterface
{
    private Swift_Mailer $mailer;
    private ParameterBagInterface $bag;
    private IcalGenerator $icalGenerator;

    public function __construct(Swift_Mailer $mailer, ParameterBagInterface $bag, IcalGenerator $icalGenerator)
    {
        $this->mailer = $mailer;
        $this->bag = $bag;
        $this->icalGenerator = $icalGenerator;
    }

    /**
     * @param EmailData[] $emailsData
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emailsData): void
    {
        foreach ($emailsData as $emailData) {
            try {
                $message = (new Swift_Message($emailData->getSubject()))
                    ->setFrom($this->bag->get('email_from'))
                    ->setTo($emailData->getTo())
                    ->setBody(
                        $this->getFormattedContent($emailData),
                        $this->selectEmailFormat($emailData->getFormat())
                    );

                if ($emailData->getReplyTo()) {
                    $message->setReplyTo($emailData->getReplyTo());
                }

                $this->setAttachment($message, $emailData);
                $this->setCal($message, $emailData);
            } catch (Exception $e) {
                throw new EmailNotSendException($e->getMessage());
            }

            $this->mailer->send($message);
        }
    }

    private function setAttachment(Swift_Message $message, EmailData $email)
    {
        if (!$email->isAttachment() || !file_exists($email->getAttachment()->getPath())) {
            return;
        }

        if (filesize($email->getAttachment()->getPath()) > $this->bag->get('max_email_attachment_file_size')) {
            return;
        }

        $message->attach(
            Swift_Attachment::fromPath($email->getAttachment()->getPath())
                ->setFilename($email->getAttachment()->getFileName())
        );

    }

    private function getFormattedContent(EmailData $email): string
    {
        if (EmailData::FORMAT_TEXT === $email->getFormat()) {
            return $email->getContent();
        }

        return HtmlTag::START_HTML . $email->getContent() . HtmlTag::END_HTML;
    }

    private function selectEmailFormat(string $format): string
    {
        if (EmailData::FORMAT_TEXT === $format) {
            return 'text/plain';
        }

        return 'text/html';
    }

    private function setCal(Swift_Message $message, EmailData $emailData)
    {
        if (!$emailData->getCalPath()) {
            return;
        }

        $message->attach(
            Swift_Attachment::fromPath($emailData->getCalPath(), IcalGenerator::CONTENT_TYPE)
                ->setFilename('cal.ics')
        );
    }
}
