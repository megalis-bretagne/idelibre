<?php


namespace App\Service\Util;


class FileUtil
{
    public function deleteFileInDirectory(string $directory)
    {
        $days = 2;
        $path = $directory;

        if ($handle = opendir($path))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (is_file($path.$file))
                {
                    if (filemtime($path.$file) < ( time() - ( $days * 24 * 60 * 60 ) ) )
                    {
                        unlink($path.$file);
                    }
                }
            }
        }
    }
}
