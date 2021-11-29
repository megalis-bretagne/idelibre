<?php

namespace App\Service\Zip;

use App\Entity\Sitting;
use App\Service\Util\DateUtil;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ZipSittingGenerator
{
    public function __construct(
        private ParameterBagInterface $bag,
        private Filesystem $filesystem,
        private DateUtil $dateUtil
    ) {
    }

    public function generateZipSitting(Sitting $sitting): string
    {
        $zip = new ZipArchive();
        $zipPath = $this->getAndCreateZipPath($sitting);
        $this->deleteZipIfAlreadyExists($zipPath);
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFile($sitting->getConvocationFile()->getPath(), $sitting->getConvocationFile()->getName());
        $this->addProjectAndAnnexesFiles($zip, $sitting);
        $zip->close();

        return $zipPath;
    }

    private function deleteZipIfAlreadyExists(string $path)
    {
        $this->filesystem->remove($path);
    }

    public function getAndCreateZipPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_zip_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.zip';
    }

    public function deleteZip(Sitting $sitting): void
    {
        $path = $this->getAndCreateZipPath($sitting);
        $this->filesystem->remove($path);
    }

    private function addProjectAndAnnexesFiles(ZipArchive $zip, Sitting $sitting): void
    {
        foreach ($sitting->getProjects() as $project) {
            $directory = 'projet_' . ($project->getRank() + 1) . '/';
            $zip->addFile($project->getFile()->getPath(), $directory . $project->getFile()->getName());
            foreach ($project->getAnnexes() as $annex) {
                $zip->addFile($annex->getFile()->getPath(), $directory . $annex->getFile()->getName());
            }
        }
    }

    public function createName(Sitting $sitting)
    {
        return $sitting->getName() . '_' . $this->dateUtil->getUnderscoredDate($sitting->getDate()) . '.zip';
    }
}
