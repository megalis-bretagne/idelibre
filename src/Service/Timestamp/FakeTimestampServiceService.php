<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;

class FakeTimestampServiceService implements TimestampServiceInterface
{
    public function signTimestamp(Timestamp $timestamp): void
    {
        $timestamp->setTsa('signed');
    }
}
