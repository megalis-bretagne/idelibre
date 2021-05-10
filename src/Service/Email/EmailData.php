<?php

namespace App\Service\Email;

class EmailData
{
    public const FORMAT_HTML = 'html';
    public const FORMAT_TEXT = 'text';

    private string $subject;
    private string $content;
    private ?string $to;
    private ?string $replyTo;
    private string $format;
    private Attachment $attachment;
    private bool $isAttachment = false;

    public function __construct(string $subject, string $content, string $format = self::FORMAT_HTML)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->format = $format;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(string $to): EmailData
    {
        $this->to = $to;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(?string $replyTo): EmailData
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    public function setAttachment(Attachment $attachment): EmailData
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function isAttachment(): bool
    {
        return $this->isAttachment ?? false;
    }

    public function setIsAttachment(bool $isAttachment): EmailData
    {
        $this->isAttachment = $isAttachment;

        return $this;
    }
}
