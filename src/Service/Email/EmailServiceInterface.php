<?php

namespace App\Service\Email;

interface EmailServiceInterface
{
    /**
     * @param EmailData[] $emails
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emails): void;
}
