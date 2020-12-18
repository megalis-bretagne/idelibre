<?php

namespace App\Service\Email;

class EmailData
{
    private string $subject;
    private string $content;
    private ?string $to;

    public function __construct(string $subject, string $content, ?string $to = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->to = $to;
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



}
