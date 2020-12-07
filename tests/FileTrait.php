<?php

namespace App\Tests;

trait FileTrait
{
    public function countFileInDirectory(string $dirPath): int
    {
        $files = glob($dirPath . "/*");

        if ($files) {
            return count($files);
        }

        return 0;
    }
}
