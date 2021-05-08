<?php

namespace App\Service\Email;

use App\Entity\EmailTemplate;
use App\Entity\User;

class EmailServiceInterfaceFake implements EmailServiceInterface
{
    /**
     * @param EmailData[] $emails
     */
    public function sendBatch(array $emails): void
    {
    }
}
