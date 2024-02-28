<?php

namespace App\Service\Base64_encoder;

use App\Entity\File;
use App\Repository\FileRepository;

class Encoder
{

    public const URL_PATTERN = '/https:\/\/\S+\.(jpg|png)/';

    public function __construct(
        private readonly FileRepository $fileRepository
    )
    {
    }


    public function imageHandler(string $content, string $structureId): callable
    {
        return function ($matches) use ($structureId) {
            foreach($matches as $match) {
                $path = File::IMG_DIR . $structureId .  '/' ;

                $pattern = self::URL_PATTERN;
                preg_match($pattern, $match, $imgPath);

                $filename = $this->extractFilename($imgPath[0]);
                $filenameWithoutExtension = $this->extractFilenameWithoutExtension($filename);

                $file = $this->fileRepository->findFileById($filenameWithoutExtension);

                copy($this->replaceUrl($imgPath[0]), $path . $filename);

                $file->setPath($path . $filename);
            }
        };
    }

    public function encodeImages(string $content, string $structureId): string
    {
        return preg_replace_callback(
            '/<img src="([^"]+)"[^>]+>/',
            $this->imgEncode($content, $structureId),
            $content
        );
    }

    private function imgEncode(string $content, string $structureId): callable
    {
//        dd($content);
        return function ($matches) use ($structureId) {
            foreach($matches as $match) {
                $path = File::IMG_DIR . $structureId .  '/' ;
                $pattern = '/https:\/\/\S+\.(jpg|png)/';
                preg_match($pattern, $match, $imgPath);

                $filename = $this->extractFilename($imgPath[0]);
                $filenameWithoutExtension = $this->extractFilenameWithoutExtension($filename);

//                $filename = array_slice(explode('/', $imgPath[0]), -1)[0];
//                $filenameWithoutExtension = array_slice(explode('.', $filename), 0, -1)[0];

                $file = $this->fileRepository->findFileById($filenameWithoutExtension);

                $extension = pathinfo($imgPath[0], PATHINFO_EXTENSION);

                $encodedImage = base64_encode(file_get_contents($file->getPath()));


                return str_replace($imgPath[0], 'data:image/' . $extension .';base64,' . $encodedImage, $match);
            }
        };
    }



    private function extractFilename(string $imgPath): string
    {
        return array_slice(explode('/', $imgPath), -1)[0];
    }

    private function extractFilenameWithoutExtension(string $filename): string
    {
        return array_slice(explode('.', $filename), 0, -1)[0];
    }

    private function replaceUrl(string $url): string
    {
        return  preg_replace(
            '/https:\/\/idelibre\.recette\.libriciel\.fr\/api\/tinymce-upload\/serve-image\//',
            '/tmp/image/',
            $url
        );
    }

}
