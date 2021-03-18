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


}
