<?php

namespace App\Service\ImageHandler;

use App\Entity\File;
use App\Repository\FileRepository;
use App\Service\File\FileManager;
use Doctrine\ORM\NonUniqueResultException;
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


    /**
     * @throws NonUniqueResultException
     */
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

    public function imgEncode(string $content, string $structureId): callable
    {
        return function ($matches) use ($structureId) {
            foreach ($matches as $match) {

                $file = $this->findFileFromId($match);
                $extension = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $encodedImage = base64_encode(file_get_contents($file->getPath()));
                return str_replace($file->getId(), 'data:image/' .$extension . ';base64,' . $encodedImage, $match);
            }
        };
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findFileFromId($match): ?File
    {
        $uuidPattern = '/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89ab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}/i';

        preg_match($uuidPattern, $match, $uuid);
        $imgId = $uuid[0] ?? null;

        return $this->fileRepository->findFileById($imgId);

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
