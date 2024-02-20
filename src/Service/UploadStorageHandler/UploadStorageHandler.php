<?php

namespace App\Service\UploadStorageHandler;

use App\Entity\Structure;

class UploadStorageHandler
{
    public function __construct()
    {
    }


    public function upload(string $content, string $imgDirPath, Structure $structure)
    {
        $this->uploadDirectory($structure);
    }

    private function uploadDirectory($structure): string
    {
        $imgDirectory = '/data/' . $structure->getId() . '/emailTemplateImages/';
        if (!file_exists($imgDirectory)) {
            mkdir($imgDirectory, 0755, true);
        }
        return $imgDirectory;
    }

}
