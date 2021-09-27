<?php

namespace App\Service\Email;

class EmailServiceInterfaceFake implements EmailServiceInterface
{
    /**
     * @param EmailData[] $emailsData
     */
    public function sendBatch(array $emailsData): void
    {
    }
}
