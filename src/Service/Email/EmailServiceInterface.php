<?php

namespace App\Service\Email;

use App\Entity\User;

interface EmailServiceInterface
{
    /**
     * @param EmailData[] $emails
     *
     * @throws EmailNotSendException
     */
    public function sendBatch(array $emails): void;
}
