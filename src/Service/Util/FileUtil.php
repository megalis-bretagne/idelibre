<?php

namespace App\Service\Util;

class FileUtil
{
    public function sanitizeName(string $fileName): string
    {
        return preg_replace('/[^a-z0-9]+/', '_', strtolower($fileName));
    }

    public function deleteFileInDirectory(string $path): void
    {
        $handle = opendir($path);
        if (!$handle) {
            return;
        }

        while (false !== ($file = readdir($handle))) {
            if ($this->isFileOlderThan2Days($path, $file)) {
                unlink($path . $file);
            }
        }
    }

    private function isFileOlderThan2Days(string $path, false|string $file): bool
    {
        if (!is_file($path . $file)) {
            return false;
        }

        return filemtime($path . $file) < (time() - (2 * 24 * 60 * 60));
    }
}
