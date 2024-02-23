<?php

namespace App\Service\UploadStorageHandler;

use App\Entity\Structure;

class UploadStorageHandler
{
    public function upload(mixed $file, Structure $structure, string $fileName): void
    {
        $imgDirPath = '/data/image/' . $structure->getId() . '/emailTemplateImages/';
        $imgPath = $imgDirPath . $fileName;

        if (!file_exists($imgDirPath)) {
            mkdir($imgDirPath, 0755, true);
        }

        file_put_contents($imgPath, $file->getContent());


    }
}
