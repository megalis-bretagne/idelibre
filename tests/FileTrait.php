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


    public function countFileLines(string $path){
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }
}
