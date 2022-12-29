<?php

namespace App\Service\Pdf;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Service\File\FileManager;
use App\Service\S3\S3Manager;
use App\Service\Util\DateUtil;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PdfSittingGenerator
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
        private readonly DateUtil $dateUtil,
        private readonly PdfChecker $checker,
        private readonly FileManager $fileManager,
        private readonly S3Manager $s3Manager,
    ) {
    }

    public function generateFullSittingPdf(Sitting $sitting): void
    {
        $pdfDocPaths = $this->getPdfDocPaths($sitting);

        $cmd = 'pdfunite ' . implode(' ', $pdfDocPaths) . ' ' . $this->getPdfPath($sitting);

        if (!$this->checker->isValid($pdfDocPaths)) {
            $this->logger->error('MergePdf : Un des PDF n\'est pas valide');

            return;
        }

        try {
            shell_exec($cmd);
        } catch (Exception $exception) {
            $this->logger->error('MergePdf : ' . $exception->getMessage());
        }

        $this->fileManager->transfertToS3($this->getPdfPath($sitting));
    }

    private function getConvocationPath(Sitting $sitting): string
    {
        return $sitting->getConvocationFile()->getPath();
    }

    /**
     * @param Project[] $projects
     */
    private function getProjectsAndAnnexesPath(iterable $projects): array
    {
        $projectsAndAnnexesArray = [];

        foreach ($projects as $project) {
            $projectsAndAnnexesArray[] = $project->getFile()->getPath();
            $projectsAndAnnexesArray = [...$projectsAndAnnexesArray, ...$this->getAnnexePath($project->getAnnexes())];
        }

        return $projectsAndAnnexesArray;
    }

    /**
     * @param Annex[] $annexes
     */
    private function getAnnexePath(iterable $annexes): array
    {
        $annexesPathArray = [];
        foreach ($annexes as $annex) {
            if ($this->isPdfFile($annex->getFile()->getName())) {
                $annexPath = $annex->getFile()->getPath();
                $annexesPathArray[] = $annexPath;
            }
        }

        return $annexesPathArray;
    }

    private function isPdfFile(string $fileName): bool
    {
        if (!strpos($fileName, '.')) {
            return false;
        }

        $exploded = (explode('.', $fileName));
        $extension = $exploded[count($exploded) - 1];

        return 'pdf' === $extension || 'PDF' === $extension;
    }

    public function getPdfPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.pdf';
    }

    public function deletePdf(Sitting $sitting): void
    {
        $path = $this->getPdfPath($sitting);

        $this->filesystem->remove($path);
        $this->s3Manager->deleteObject($path);
    }

    public function createPrettyName(Sitting $sitting): string
    {
        return $sitting->getName() . '_' . $this->dateUtil->getUnderscoredDate($sitting->getDate()) . '.pdf';
    }

    /**
     * @return array<string>
     */
    public function getPdfDocPaths(Sitting $sitting): array
    {
        $pdfDocPaths = [];
        $pdfDocPaths[] = $this->getConvocationPath($sitting);

        return [...$pdfDocPaths, ...$this->getProjectsAndAnnexesPath($sitting->getProjects())];
    }
}
