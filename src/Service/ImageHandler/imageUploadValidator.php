<?php

namespace App\Service\ImageHandler;

use App\Entity\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class imageUploadValidator
{
    public const MAX_FILESIZE = 200000; // 200 kB

    public function isMissingFile($file): UploadedFile|Response
    {
        if (!$file) {
            return new Response("Missing file.", 400);
        }

        return $file;
    }

    public function isTooBig($file): UploadedFile|Response
    {
        if ($file->getSize() > self::MAX_FILESIZE) {
            return new Response  ("Le poids maximal de l'image doit être de 200Kb : " . (self::MAX_FILESIZE / 1000000) . "MB", 400);
        }

        return $file;
    }

    public function isNotImage($file): UploadedFile|Response
    {
        if (!str_starts_with($file->getMimeType(), "image/")) {
            return new Response("Le fichier doit être au format jpeg,jpg ou png.", 400);
        }

        return $file;
    }

    public function tooManyImages(string $content): bool
    {
        return preg_match_all('/<img src="([^"]+)"[^>]+>/', $content, $images) > 5;
    }
}
