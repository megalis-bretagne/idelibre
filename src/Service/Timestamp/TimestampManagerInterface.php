<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;

interface TimestampManagerInterface
{
    public function signTimestamp(Timestamp $timestamp);
}
