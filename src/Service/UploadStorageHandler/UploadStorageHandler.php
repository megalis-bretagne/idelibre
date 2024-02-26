<?php

namespace App\Service\UploadStorageHandler;

use App\Entity\Structure;


class UploadStorageHandler
{

    public function upload(mixed $file, Structure $structure, string $filename): void
    {
        $tmpDirPath = '/tmp/image/' . $structure->getId() . '/';
        $dirPath = '/data/image/' . $structure->getId() . '/';

        if (!file_exists($tmpDirPath)) {
            mkdir($tmpDirPath, 0755, true);
        }
        if (!file_exists($dirPath)) {
            mkdir($tmpDirPath, 0755, true);
        }

        file_put_contents($tmpDirPath . $filename , $file->getContent());
        copy($tmpDirPath . $filename, $dirPath . $filename);
    }

  }
