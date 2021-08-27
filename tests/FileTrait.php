<?php

namespace App\Tests;

use Symfony\Component\Filesystem\Filesystem;

trait FileTrait
{
    public function countFileInDirectory(string $dirPath): int
    {
        $files = glob($dirPath . '/*');

        if ($files) {
            return count($files);
        }

        return 0;
    }

    public function deleteFileInDirectory(string $dirPath)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($dirPath);
    }

    public function countFileLines(string $path)
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }
}
