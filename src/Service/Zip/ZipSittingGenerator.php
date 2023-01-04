<?php

namespace App\Service\Zip;

use App\Entity\GeneratedFile;
use App\Entity\Sitting;
use App\Service\GeneratedFile\GeneratedFileManager;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\File\FileManager;
use App\Service\S3\S3Manager;
use App\Service\Util\DateUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ZipSittingGenerator
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly Filesystem $filesystem,
        private readonly DateUtil $dateUtil,
        private readonly ZipChecker $checker,
        private readonly PdfSittingGenerator $generator,
        private readonly LoggerInterface $logger,
        private readonly FileManager $fileManager,
        private readonly S3Manager $s3Manager,
        private readonly GeneratedFileManager $generatedFileManager,
    ) {
    }

    public function generateZipSitting(Sitting $sitting): string
    {
        $this->deleteZip($sitting);

        $pdfDocPaths = $this->generator->getPdfDocPaths($sitting);

        foreach ($pdfDocPaths as $pdfDocPath) {
            if (!$this->fileManager->fileExist($pdfDocPath)) {
                $this->fileManager->downloadToS3($pdfDocPath);
            }
        }

        if (!$this->checker->isValid($pdfDocPaths)) {
            $this->logger->error('PDF is too heavy, max size is' . $this->bag->get('maximum_size_pdf_zip_generation'));

            return  'no zip created';
        }

        $zip = new ZipArchive();

        $zipPath = $this->getAndCreateZipPath($sitting);
        $this->deleteZipIfAlreadyExists($zipPath);
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFile($sitting->getConvocationFile()->getPath(), $sitting->getConvocationFile()->getName());
        $this->addProjectAndAnnexesFiles($zip, $sitting);
        $zip->close();

        $this->generatedFileManager->addOrReplace(
            GeneratedFile::ZIP,
            $sitting,
            $zipPath
        );

        $this->fileManager->transfertToS3($zipPath);

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

        $this->generatedFileManager->deleteGeneratedFile($sitting, GeneratedFile::ZIP);

        $this->filesystem->remove($path);
        $this->s3Manager->deleteObject($path);
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
