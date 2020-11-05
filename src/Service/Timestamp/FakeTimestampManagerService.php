<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;

class FakeTimestampManagerService implements TimestampManagerInterface
{
    public function signTimestamp(Timestamp $timestamp)
    {
        $timestamp->setTsa('signed');
    }
}
