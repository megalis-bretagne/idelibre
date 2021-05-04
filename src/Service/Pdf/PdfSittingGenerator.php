<?php

namespace App\Service\Pdf;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use iio\libmergepdf\Merger;
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
        $merger = new Merger();
        $this->addConvocation($merger, $sitting);
        $this->addProjectsAndAnnexes($merger, $sitting->getProjects());
        $merged = $merger->merge();
        file_put_contents($this->getPdfPath($sitting), $merged);
    }

    private function addConvocation(Merger $merger, Sitting $sitting): void
    {
        $merger->addFile($sitting->getConvocationFile()->getPath());
    }

    /**
     * @param Project[] $projects
     */
    private function addProjectsAndAnnexes(Merger $merger, iterable $projects): void
    {
        foreach ($projects as $project) {
            $merger->addFile($project->getFile()->getPath());
            $this->addAnnexes($merger, $project->getAnnexes());
        }
    }

    /**
     * @param Annex[] $annexes
     */
    private function addAnnexes(Merger $merger, iterable $annexes): void
    {
        foreach ($annexes as $annex) {
            if ($this->isPdfFile($annex->getFile()->getName())) {
                $merger->addFile($annex->getFile()->getPath());
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

        return 'pdf' === $extension || 'PDF' === $extension;
    }

    public function getPdfPath(Sitting $sitting): string
    {
        $directoryPath = $this->bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId();
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath . '/' . $sitting->getId() . '.pdf';
    }
}
