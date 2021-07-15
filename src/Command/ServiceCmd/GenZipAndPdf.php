<?php

namespace App\Command\ServiceCmd;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\Zip\ZipSittingGenerator;
use DateTime;

class GenZipAndPdf
{
    private StructureRepository $structureRepository;
    private SittingRepository $sittingRepository;
    private PdfSittingGenerator $pdfSittingGenerator;
    private ZipSittingGenerator $zipSittingGenerator;

    public function __construct(
        StructureRepository $structureRepository,
        SittingRepository $sittingRepository,
        PdfSittingGenerator $pdfSittingGenerator,
        ZipSittingGenerator $zipSittingGenerator
    ) {
        $this->structureRepository = $structureRepository;
        $this->sittingRepository = $sittingRepository;
        $this->pdfSittingGenerator = $pdfSittingGenerator;
        $this->zipSittingGenerator = $zipSittingGenerator;
    }

    /**
     * @return Structure[]
     */
    private function listStructures(): array
    {
        return $this->structureRepository->findAll();
    }

    /**
     * @return Sitting[]
     */
    private function listActiveSittingsByStructure(Structure $structure): array
    {
        return $this->sittingRepository->findActiveSittingsAfterDate($structure, new DateTime('- 4month'));
    }

    public function genAllZipPdf()
    {
        foreach ($this->listStructures() as $structure) {
            $this->genZipAndPdfByStructure($structure);
        }
    }

    public function genZipAndPdfByStructure(Structure $structure)
    {
        dump('_________________________');
        dump('Structure : ' . $structure->getName());
        dump('Sitting count : ' . count($this->listActiveSittingsByStructure($structure)));

        foreach ($this->listActiveSittingsByStructure($structure) as $key => $sitting) {
            dump('Sitting num : ' . $key);
            $this->pdfSittingGenerator->generateFullSittingPdf($sitting);
            $this->zipSittingGenerator->generateZipSitting($sitting);
        }
    }
}
