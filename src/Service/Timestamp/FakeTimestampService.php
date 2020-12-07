<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;

class FakeTimestampService implements TimestampServiceInterface
{

    public function signTimestamp(Timestamp $timestamp): string
    {
        return file_put_contents('signed', $this->getTimestampDirectory($timestamp));

    }

    /**
     * @throws TimestampException
     */
    private function getTimestampDirectory(Timestamp $timestamp): string
    {
        if(!$timestamp->getFilePathContent()) {
            throw new TimestampException('missing timestamp content path');
        }

        return dirname($timestamp->getFilePathContent());
    }
}
