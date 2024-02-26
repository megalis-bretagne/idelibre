<?php

namespace App\Service\Base64_encoder;

use App\Repository\FileRepository;

class Encoder
{

    public function __construct(
        private readonly FileRepository $fileRepository
    )
    {
    }

    public function encodeImages(string $content): string
    {
        return preg_replace_callback(
            '/<img src="([^"]+)"[^>]+>/',
            $this->imgEncode($content),
            $content
        );
    }

    private function imgEncode(string $content): callable
    {
        return function ($matches) {
            foreach($matches as $match) {
                $pattern = '/https:\/\/\S+\.(jpg|png)/';
                preg_match($pattern, $match, $imgPath);

                $filename = array_slice(explode('/', $imgPath[0]), -1)[0];
                $filenameWithoutExtension = array_slice(explode('.', $filename), 0, -1)[0];

                $file = $this->fileRepository->findFileById($filenameWithoutExtension);
                //dd($file);
                $extension = pathinfo($imgPath[0], PATHINFO_EXTENSION);

                $encodedImage = base64_encode(file_get_contents($file->getPath()));

//                dd($encodedImage);

                return str_replace($imgPath[0], 'data:image/' . $extension .';base64,' . $encodedImage, $match);
            }
        };
    }

}
