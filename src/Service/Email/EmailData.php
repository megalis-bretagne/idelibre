<?php

namespace App\Service\Email;

class EmailData
{
    const TYPE_HTML = 'html';
    const TYPE_TEXT = 'text';

    private string $subject;
    private string $content;
    private ?string $to;
    private ?string $replyTo;

    public function __construct(string $subject, string $content, ?string $to = null, ?string $replyTo = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->to = $to;
        $this->replyTo = $replyTo;
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
}
