<?php

namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TimestampManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TimestampContentFileGenerator $contentGenerator,
        private readonly LshorodatageInterface $lshorodatage,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function delete(Timestamp $timestamp): void
    {
        $this->em->remove($timestamp);
    }



    public function saveTimestampInFile(StreamInterface $tsToken, string $path): string
    {
        $pathTsa = $path . '.tsa';
        file_put_contents($pathTsa, $tsToken);

        return $pathTsa;
    }


    /**
     * @throws Exception
     */
    public function listTimeStamps(string $directoryPath): array
    {
        if (!$this->filesystem->exists($directoryPath)) {
            throw new NotFoundHttpException("Aucun jeton n'a été trouvé pour cette séance");
        }

        $paths = array_map(fn ($file) => $directoryPath . $file, array_diff(scandir($directoryPath), ['.', '..']));

        return $this->groupByTwo($paths);
    }

    private function groupByTwo(array $files): array
    {
        $temp = [];
        foreach ($files as $file) {
            if (str_contains($file, '.tsa')) {
                $baseName = substr($file, 0, -4);
                $temp[$baseName]['tsa'] = $file;
            } else {
                $temp[$file]['file'] = $file;
            }
        }

        $groupByTwo = [];

        foreach ($temp as $filePair) {
            if (isset($filePair['file']) && isset($filePair['tsa'])) {
                $groupByTwo[] = ['file' => $filePair['file'], 'tsa' => $filePair['tsa']];
            }
        }

        return $groupByTwo;
    }

    /**
     * @throws LsHorodatageException
     */
    public function extractTsaInfos(array $timestamps): array
    {
        $tsaInfos = [];
        foreach ($timestamps as $timestamp) {
            $tsaInfos[] = [
                'tsaFilename' => str_replace(".tsa", "", pathinfo($timestamp['tsa'], PATHINFO_BASENAME)),
                'tsaContent' => $this->lshorodatage->readTimestampToken($timestamp['tsa']),
                'content' => file_get_contents($timestamp['file']),
                'isValid' => $this->lshorodatage->verifyTimestampToken($timestamp['file'], $timestamp['tsa']),
            ];
        }
        return $tsaInfos;
    }
}
