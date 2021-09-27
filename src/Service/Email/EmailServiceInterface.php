<?php

namespace App\Service\Email;

interface EmailServiceInterface
{
    /**
     * @param EmailData[] $emailsData
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emailsData): void;
}
