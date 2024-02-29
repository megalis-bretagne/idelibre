<?php

namespace App\Service\Base64_encoder;

use App\Entity\File;
use App\Repository\FileRepository;
use App\Service\File\FileManager;
use Symfony\Component\Filesystem\Filesystem;

class Encoder
{

    public const URL_PATTERN = '/https:\/\/\S+\.(jpg|png)/';

    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly FileManager    $fileManager,
        private readonly Filesystem     $filesystem
    )
    {
    }


    public function imageHandlerAndUpdateContent(string $content, string $structureId): string
    {
        preg_match_all(self::URL_PATTERN, $content, $imagesSrc);
        if (!isset($imagesSrc[0])) {
            return $content;
        }

        foreach ($imagesSrc[0] as $imageSrc) {
            $filename = $this->extractFilename($imageSrc);
            $fileId = $this->extractFilenameWithoutExtension($filename);
            $content = $this->replaceSrcUrlContent($imageSrc, $fileId ,$content);

            $path = File::IMG_DIR . $structureId . '/' . $filename;
            $this->filesystem->copy("/tmp/image/{$structureId}/{$filename}", $path);

            $file = $this->fileRepository->findFileById($fileId);
            $this->fileManager->saveFinalImg($file, $path);
        }

        return $content;
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
        return function ($matches) use ($structureId) {
            foreach ($matches as $match) {
                $path = File::IMG_DIR . $structureId . '/';
                $pattern = '/https:\/\/\S+\.(jpg|png)/';
                preg_match($pattern, $match, $imgPath);

                $filename = $this->extractFilename($imgPath[0]);
                $filenameWithoutExtension = $this->extractFilenameWithoutExtension($filename);

                $file = $this->fileRepository->findFileById($filenameWithoutExtension);

                $extension = pathinfo($imgPath[0], PATHINFO_EXTENSION);

                $encodedImage = base64_encode(file_get_contents($file->getPath()));

                return str_replace($imgPath[0], 'data:image/' . $extension . ';base64,' . $encodedImage, $match);
            }
        };
    }


    private function extractFilename(string $imgPath): string
    {
        return array_slice(explode('/', $imgPath), -1)[0];
    }

    private function extractFilenameWithoutExtension(string $filename): string
    {
        return explode('.', $filename)[0];
    }

    private function replaceSrcUrlContent(string $url, string $fileName, string $content): string
    {
        return str_replace($url, $fileName, $content);
    }

}
