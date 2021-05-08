<?php

namespace App\Service\Email;

class EmailServiceInterfaceFake implements EmailServiceInterface
{
    /**
     * @param EmailData[] $emails
     */
    public function sendBatch(array $emails): void
    {
    }
}
