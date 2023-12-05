<?php

namespace App\Service\File\Generator;

use App\Entity\Sitting;
use App\Service\Pdf\PdfValidator;
use App\Service\Util\DateUtil;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class FileGenerator
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly Filesystem $filesystem,
        private readonly DateUtil $dateUtil,
        private readonly FileChecker $fileChecker,
        private readonly LoggerInterface $logger,
        private readonly PdfValidator $pdfValidator,
    ) {
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function genFullSittingPdf(Sitting $sitting): void
    {
        $this->deleteFullSittingFile($sitting, 'pdf');

        $fullSittingDocsPath = $this->getFullSittingDocsPath($sitting);
        $pdfDocsPathFinal = $this->genFullSittingDirPath($sitting, 'pdf');

        if (!$this->fileChecker->isValid('pdf', $fullSittingDocsPath)) {
            $this->logger->error('PDF is too heavy, max size is' . $this->bag->get('maximum_size_pdf_zip_generation'));

            return;
        }

        $cmd = 'pdfunite ' . implode(' ', $fullSittingDocsPath) . ' ' . $pdfDocsPathFinal;
        try {
            shell_exec($cmd);
        } catch (Exception $exception) {
            $this->logger->error('PDF Merging : ' . $exception->getMessage());
        }
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function genFullSittingZip(Sitting $sitting): string
    {

        $this->deleteZipIfAlreadyExists($this->genFullSittingDirPath($sitting, 'zip'));

        if (!$this->fileChecker->isValid('zip', null, $sitting)) {
            $this->logger->error('zip is too heavy, max size is' . $this->bag->get('maximum_size_pdf_zip_generation'));

            return 'no zip created';
        }

        $zip = new ZipArchive();
        $zipPath = $this->genFullSittingDirPath($sitting, 'zip');
        $this->deleteZipIfAlreadyExists($zipPath);
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFile($sitting->getConvocationFile()->getPath(), $sitting->getConvocationFile()->getName());
        $this->addProjectAndAnnexesFiles($zip, $sitting);
        $this->addOtherDocs($zip, $sitting);
        $zip->close();

        return $zipPath;
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function getDirectoryPathByExtension(string $extension): string
    {
        return match ($extension) {
            'zip' => $this->bag->get('document_full_zip_directory'),
            'pdf' => $this->bag->get('document_full_pdf_directory'),
            default => throw new UnsupportedExtensionException('This type is not supported')
        };
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function genFullSittingDirPath(Sitting $sitting, string $extension): string
    {
        $directoryPath = $this->getDirectoryPathByExtension($extension) . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.' . $extension;
    }

    /**
     * @return array<string>
     */
    private function getFullSittingDocsPath(Sitting $sitting): array
    {
        $fullSittingDocsPath = [];

        $fullSittingDocsPath[] = $this->getConvocationPath($sitting);



        return [...$fullSittingDocsPath, ...$this->getProjectsAndAnnexesPath($sitting->getProjects())];
    }

    private function getConvocationPath(Sitting $sitting): string
    {
        return $sitting->getConvocationFile()->getPath();
    }

    /**
     * @return array<string>
     */
    private function getProjectsAndAnnexesPath(iterable $projects): array
    {
        $projectsAndAnnexesPaths = [];

        foreach ($projects as $project) {
            $projectsAndAnnexesPaths[] = $project->getFile()->getPath();
            $projectsAndAnnexesPaths = [...$projectsAndAnnexesPaths, ...$this->getAnnexesPath($project->getAnnexes())];
        }

        return $projectsAndAnnexesPaths;
    }

    /**
     * @return array<string>
     */
    private function getAnnexesPath(iterable $annexes): array
    {
        $annexesPaths = [];

        foreach ($annexes as $annex) {
            if ($this->pdfValidator->isPdfFile($annex->getFile()->getName())) {
                $annexesPaths[] = $annex->getFile()->getPath();
            }
        }

        return $annexesPaths;
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

    private function addOtherDocs(ZipArchive $zip, Sitting $sitting): void
    {
        foreach ($sitting->getOtherdocs() as $doc) {
            $directory = 'documents_' . ($doc->getRank() + 1) . '/';
            $zip->addFile($doc->getFile()->getPath(), $directory . $doc->getFile()->getName());
        }
    }

    private function deleteZipIfAlreadyExists(string $path): void
    {
        $this->filesystem->remove($path);
    }

    public function deleteFullSittingFile(Sitting $sitting, string $extension): void
    {
        $path = $this->genFullSittingDirPath($sitting, $extension);
        $this->filesystem->remove($path);
    }

    public function createPrettyName(Sitting $sitting, string $extension): string
    {
        return $sitting->getName() . '_' . $this->dateUtil->getUnderscoredDate($sitting->getDate()) . '.' . $extension;
    }
}
