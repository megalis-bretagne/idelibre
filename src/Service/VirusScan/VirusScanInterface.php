<?php

namespace App\Service\VirusScan;

interface VirusScanInterface
{
    /**
     * @param $filePath
     * @param bool $removeUnsafe autodelete file if virus
     *
     * @throws VirusScanException
     */
    public function isFileSafe($filePath, $removeUnsafe = true): bool;
}
