<?php

namespace App\Service\Email;

class EmailServiceFactory
{
    public const MAILJET = 'mailjet';

    public function __construct(
        private SimpleEmailService $simpleEmailService,
        private MailjetService $mailjetService
    ) {
    }

    public function chooseImplementation(): EmailServiceInterface
    {
        if (self::MAILJET === getenv('MAILER_TYPE')) {
            return $this->mailjetService;
        }

        return $this->simpleEmailService;
    }
}
