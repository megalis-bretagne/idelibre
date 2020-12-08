<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;

interface TimestampServiceInterface
{
    public function signTimestamp(Timestamp $timestamp): string;
}
