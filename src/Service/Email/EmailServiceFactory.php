<?php

namespace App\Service\Email;

class EmailServiceFactory
{
    public const MAILJET = 'mailjet';

    private SimpleEmailService $simpleEmailService;
    private MailjetService $mailjetService;

    public function __construct(SimpleEmailService $simpleEmailService, MailjetService $mailjetService)
    {
        $this->simpleEmailService = $simpleEmailService;
        $this->mailjetService = $mailjetService;
    }

    public function chooseImplementation(): EmailServiceInterface
    {
        if (self::MAILJET === getenv('MAILER_TYPE')) {
            return $this->mailjetService;
        }

        return $this->simpleEmailService;
    }
}
