<?php


namespace App\Service\VirusScan;

class VirusScanFake implements VirusScanInterface
{
    /**
     * @inheritDoc
     */
    public function isFileSafe($filePath, $removeUnsafe = true): bool
    {
        return true;
    }
}
