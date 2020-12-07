<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;

class FakeTimestampService implements TimestampServiceInterface
{
    public function signTimestamp(Timestamp $timestamp): string
    {
        if (!$timestamp->getFilePathContent()) {
            throw new TimestampException('missing timestamp content path');
        }

        $pathTsa = $timestamp->getFilePathContent() . ".tsa";
        file_put_contents($pathTsa, 'signed');

        return $pathTsa;
    }
}
