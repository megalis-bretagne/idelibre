<?php

namespace App\Service\Email;

use App\Entity\User;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\HtmlTag;
use App\Service\EmailTemplate\TemplateTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SimpleEmailService implements EmailServiceInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $bag,
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

    public function sendInitPassword(User $user, string $token): void
    {
        $contentSubject = '[ ' . TemplateTag::PRODUCT_NAME . '] Initialisation de votre mot de passe';
        $subject = $this->emailGenerator->generateSubject($user, $contentSubject);

        $contents = $this->emailGenerator->generateInitPassword(
            $user,
            $token
        );

        $this->send($user->getEmail(), $subject, $contents['html'], $contents['text']);
    }

    public function sendResetPassword(User $user, string $token): void
    {
        $contentSubject = '[ ' . TemplateTag::PRODUCT_NAME . '] Réinitialiser votre mot de passe';
        $subject = $this->emailGenerator->generateSubject($user, $contentSubject);

        $contents = $this->emailGenerator->generateForgetPassword(
            $user,
            $token
        );

        $this->send($user->getEmail(), $subject, $contents['html'], $contents['text']);
    }

    public function sendReloadPassword(User $user, string $token): void
    {
        $contentSubject = '[ ' . TemplateTag::PRODUCT_NAME . '] Demande de réinitilisation par un administrateur';
        $subject = $this->emailGenerator->generateSubject($user, $contentSubject);

        $contents = $this->emailGenerator->generateReloadPassword(
            $user,
            $token
        );

        $this->send($user->getEmail(), $subject, $contents['html'], $contents['text']);
    }

    private function send(string $to, string $subject, string $contentHtml, string $contentText): void
    {
        $email = (new Email())
            ->from($this->bag->get('email_from'))
            ->to($to)
            ->subject($subject)
        ;

        $email->html($contentHtml);
        $email->text($contentText);

        $this->mailer->send($email);
    }
}
