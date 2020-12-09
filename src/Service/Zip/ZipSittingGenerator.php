<?php

namespace App\Service\Zip;

use App\Entity\Sitting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ZipSittingGenerator
{
    private ParameterBagInterface $bag;
    private Filesystem $filesystem;

    public function __construct(ParameterBagInterface $bag, Filesystem $filesystem)
    {
        $this->bag = $bag;
        $this->filesystem = $filesystem;
    }

    public function generateZipSitting(Sitting $sitting): string
    {
        $zip = new ZipArchive();
        $zipPath = $this->getAndCreateZipPath($sitting);
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFile($sitting->getFile()->getPath(), $sitting->getFile()->getName());
        $this->addProjectAndAnnexesFiles($zip, $sitting);
        $zip->close();

        return $zipPath;
    }

    public function getAndCreateZipPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_zip_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.zip';
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
}
