<?php

namespace App\Service\Util;

class FileUtil
{
    public function deleteFileInDirectory(string $path)
    {
        $handle = opendir($path);
        if (!$handle) {
            return;
        }

        while (false !== ($file = readdir($handle))) {
            if($this->isFileOlderThan2Days($path, $file)) {
                unlink($path . $file);
            }
        }
    }

    private function isFileOlderThan2Days(string $path, false|string $file):bool
    {
        if (!is_file($path . $file)){
            return false;
        }

        return filemtime($path . $file) < (time() - (2 * 24 * 60 * 60));
    }
}
