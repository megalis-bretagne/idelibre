<?php

namespace App\Service\ImageHandler;

use App\Entity\Structure;


class UploadStorageHandler
{

    public function upload(mixed $file, Structure $structure, string $filename): void
    {
        $this->createImgDirectory($structure);
        file_put_contents($this->createTmpDirectory($structure) . $filename , $file->getContent());
    }

    private function createTmpDirectory(Structure $structure): string
    {
        $tmpDirPath = '/tmp/image/' . $structure->getId() . '/';

        if (!file_exists($tmpDirPath)) {
            mkdir($tmpDirPath, 0755, true);
        }

        return $tmpDirPath;
    }

    private function createImgDirectory(Structure $structure): void
    {
        $dirPath = '/data/image/' . $structure->getId() . '/';

        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
    }

  }
