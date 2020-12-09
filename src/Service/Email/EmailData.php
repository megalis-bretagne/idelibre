<?php

namespace App\Service\Email;

class EmailData
{
    private string $subject;
    private string $content;

    public function __construct(string $subject, string $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
