<?php

namespace App\Service\Email;

use App\Service\EmailTemplate\HtmlTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SimpleEmailService implements EmailServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $bag
    ) {
    }

    /**
     * @param EmailData[] $emailsData
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emailsData): void
    {
        foreach ($emailsData as $emailData) {
            $email = (new Email())
                ->from($this->bag->get('email_from'))
                ->to($emailData->getTo())
                ->subject($emailData->getSubject());

            if ($emailData->getReplyTo()) {
                $email->replyTo($emailData->getReplyTo());
            }

            $this->setContent($email, $emailData);
            $this->setAttachment($email, $emailData);
            $this->setCal($email, $emailData);

            try {
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                throw new EmailNotSendException($e->getMessage());
            }
        }
    }

    private function setAttachment(Email $email, EmailData $emailData)
    {
        if (!$emailData->isAttachment() || !file_exists($emailData->getAttachment()->getPath())) {
            return;
        }

        if (filesize($emailData->getAttachment()->getPath()) > $this->bag->get('max_email_attachment_file_size')) {
            return;
        }

        $email->attachFromPath($emailData->getAttachment()->getPath(), $emailData->getAttachment()->getFileName());
    }

    private function setCal(Email $email, EmailData $emailData)
    {
        if (!$emailData->getCalPath()) {
            return;
        }

        $email->attachFromPath(
            $emailData->getCalPath(),
            'cal.ics',
            CalGenerator::CONTENT_TYPE
        );
    }

    private function setContent(Email $email, EmailData $emailData)
    {
        if (EmailData::FORMAT_TEXT === $emailData->getFormat()) {
            $email->text($emailData->getContent());
        }

        if (EmailData::FORMAT_HTML === $emailData->getFormat()) {
            $contentHtml = HtmlTag::START_HTML . $emailData->getContent() . HtmlTag::END_HTML;
            $email->html($contentHtml);
        }
    }
}
