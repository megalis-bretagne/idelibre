<?php

namespace App\Service\Pdf;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PdfSittingGenerator
{
    public function __construct(
        private ParameterBagInterface $bag,
        private Filesystem $filesystem,
        private LoggerInterface $logger
    ) {
    }

    public function generateFullSittingPdf(Sitting $sitting): void
    {
        $cmd = 'pdftk ';
        $cmd = $this->addConvocation($cmd, $sitting);
        $cmd = $this->addProjectsAndAnnexes($cmd, $sitting->getProjects());
        $cmd .= ' cat output ' . $this->getPdfPath($sitting);
        try {
            shell_exec($cmd);
        } catch (Exception $exception) {
            $this->logger->error('MergePdf : ' . $exception->getMessage());
        }
    }

    private function addConvocation(string $cmd, Sitting $sitting): string
    {
        return $cmd . $sitting->getConvocationFile()->getPath() . ' ';
    }

    /**
     * @param Project[] $projects
     */
    private function addProjectsAndAnnexes(string $cmd, iterable $projects): string
    {
        foreach ($projects as $project) {
            $cmd .= $project->getFile()->getPath() . ' ';
            $cmd = $this->addAnnexes($cmd, $project->getAnnexes());
        }

        return $cmd;
    }

    /**
     * @param Annex[] $annexes
     */
    private function addAnnexes(string $cmd, iterable $annexes): string
    {
        foreach ($annexes as $annex) {
            if ($this->isPdfFile($annex->getFile()->getName())) {
                $cmd .= $annex->getFile()->getPath() . ' ';
            }
        }

        return $cmd;
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
    }
}
