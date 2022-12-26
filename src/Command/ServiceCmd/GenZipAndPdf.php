<?php

namespace App\Command\ServiceCmd;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\Zip\ZipSittingGenerator;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;

class GenZipAndPdf
{
    public function __construct(
        private readonly StructureRepository $structureRepository,
        private readonly SittingRepository $sittingRepository,
        private readonly PdfSittingGenerator $pdfSittingGenerator,
        private readonly ZipSittingGenerator $zipSittingGenerator
    ) {
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

    public function genAllTimeZipPdfByStructureId(string $structureId)
    {
        $structure = $this->structureRepository->find($structureId);
        if (!$structure) {
            throw new EntityNotFoundException("Structure with id {$structureId} does not exist");
        }

        $sittings = $this->sittingRepository->findByStructure($structure)->getQuery()->getResult();

        foreach ($sittings as $key => $sitting) {
            dump('Sitting num : ' . $key);
            $this->pdfSittingGenerator->generateFullSittingPdf($sitting);
            $this->zipSittingGenerator->generateZipSitting($sitting);
        }
    }
}
