<?php

namespace App\Service\Pdf;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use mikehaertl\pdftk\Pdf;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PdfSittingGenerator
{
    private ParameterBagInterface $bag;
    private Filesystem $filesystem;

    public function __construct(ParameterBagInterface $bag, Filesystem $filesystem)
    {
        $this->bag = $bag;
        $this->filesystem = $filesystem;
    }

    public function generateFullSittingPdf(Sitting $sitting): void
    {
        $pdf = new Pdf();
        $this->addConvocation($pdf, $sitting);
        $this->addProjectsAndAnnexes($pdf, $sitting->getProjects());
        $pdf->saveAs($this->getPdfPath($sitting));
    }

    private function addConvocation(Pdf $pdf, Sitting $sitting): void
    {
        $pdf->addFile($sitting->getFile()->getPath());
    }

    /**
     * @param Pdf $pdf
     * @param Project[] $projects
     */
    private function addProjectsAndAnnexes(Pdf $pdf, iterable $projects): void
    {
        foreach ($projects as $project) {
            $pdf->addFile($project->getFile()->getPath());
            $this->addAnnexes($pdf, $project->getAnnexes());
        }
    }

    /**
     * @param Annex[] $annexes
     */
    private function addAnnexes(Pdf $pdf, iterable $annexes): void
    {
        foreach ($annexes as $annex) {
            if ($this->isPdfFile($annex->getFile()->getName())) {
                $pdf->addFile($annex->getFile()->getPath());
            }
        }
    }

    private function isPdfFile(string $fileName): bool
    {
        if (!strpos($fileName, '.')) {
            return false;
        }

        $exploded = (explode('.', $fileName));
        $extension = $exploded[count($exploded) - 1];

        return $extension === 'pdf' || $extension === 'PDF';
    }

    public function getPdfPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.pdf';
    }
}
