<?php

namespace App\Service\File\Generator;

use App\Entity\Sitting;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class SittingZipGenerator
{
    public function __construct(
        private readonly Filesystem            $filesystem,
        private readonly ParameterBagInterface $bag,
        private readonly FileChecker           $fileChecker,
        private readonly LoggerInterface       $logger,
        private readonly ProjectRepository     $projectRepository,
        private readonly OtherdocRepository    $otherdocRepository,
    ) {
    }

    public function genZip(Sitting $sitting): string
    {
        $zipPath = $this->genZipSittingDirPath($sitting);

        $this->deleteZipIfAlreadyExists($zipPath);

        if (!$this->fileChecker->isValid('zip', null, $sitting)) {
            $this->logger->error('zip is too heavy, max size is' . $this->bag->get('maximum_size_pdf_zip_generation'));
        }

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        $this->addConvocation($sitting, $zip);

        $this->addProjectWithAnnexes($sitting, $zip);

        $this->addOtherDocs($sitting, $zip);

        $zip->close();

        return $zipPath;
    }

    private function genZipSittingDirPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_full_zip_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.zip';
    }


    private function deleteZipIfAlreadyExists(string $path): void
    {
        $this->filesystem->remove($path);
    }

    private function addConvocation(Sitting $sitting, ZipArchive $zip): void
    {
        $zip->addFile($sitting->getConvocationFile()->getPath(), '00__Convocation.pdf');
    }

    private function addProjectWithAnnexes(Sitting $sitting, ZipArchive $zip): void
    {
        $projects = $this->projectRepository->getProjectsBySitting($sitting);
        foreach ($projects as $project) {
            $cleanedName = $this->cleanFileName($project->getName());
            $filename = sprintf(
                "%02d__%s.%s",
                $project->getRank() + 1,
                $cleanedName,
                pathinfo($project->getFile()->getName(), PATHINFO_EXTENSION)
            );

            $zip->addFile($project->getFile()->getPath(), $filename);

            foreach ($project->getAnnexes() as $annex) {
                $filename = sprintf(
                    "%02d_%02d__%s",
                    $project->getRank() + 1,
                    $annex->getRank() + 1,
                    $annex->getFile()->getName()
                );

                $zip->addFile($annex->getfile()->getPath(), $filename);
            }
        }
    }


    private function cleanFileName(string $name): string
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);

        return substr($cleaned, 0, 80);
    }

    private function addOtherDocs(Sitting $sitting, ZipArchive $zip): void
    {
        $otherDocs = $this->otherdocRepository->getOtherDocsBySitting($sitting);
        foreach ($otherDocs as $otherDoc) {
            $cleanedName = $this->cleanFileName($otherDoc->getName());
            $filename = sprintf(
                "DOC-%02d__%s.%s",
                $otherDoc->getRank() + 1,
                $cleanedName,
                pathinfo($otherDoc->getFile()->getName(), PATHINFO_EXTENSION)
            );

            $zip->addFile($otherDoc->getFile()->getPath(), $filename);
        }
    }
}
