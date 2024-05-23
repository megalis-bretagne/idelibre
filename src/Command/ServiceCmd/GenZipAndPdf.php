<?php

namespace App\Command\ServiceCmd;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\SittingZipGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;

class GenZipAndPdf
{
    public function __construct(
        private readonly StructureRepository $structureRepository,
        private readonly SittingRepository $sittingRepository,
        private readonly FileGenerator $fileGenerator,
        private readonly SittingZipGenerator $sittingZipGenerator,
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
        return $this->sittingRepository->findActiveSittingsAfterDateByStructure($structure, new DateTime('- 4month'));
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function genAllZipPdf(): void
    {
        foreach ($this->listStructures() as $structure) {
            $this->genZipAndPdfByStructure($structure);
        }
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function genZipAndPdfByStructure(Structure $structure): void
    {
        dump('_________________________');
        dump('Structure : ' . $structure->getName());
        dump('Sitting count : ' . count($this->listActiveSittingsByStructure($structure)));

        foreach ($this->listActiveSittingsByStructure($structure) as $key => $sitting) {
            dump('Sitting num : ' . $key);
            $this->fileGenerator->genFullSittingPdf($sitting);
            $this->sittingZipGenerator->genZip($sitting);
        }
    }

    public function genAllTimeZipPdfByStructureId(string $structureId): void
    {
        $structure = $this->structureRepository->find($structureId);
        if (!$structure) {
            throw new EntityNotFoundException("Structure with id {$structureId} does not exist");
        }

        $sittings = $this->sittingRepository->findByStructure($structure)->getQuery()->getResult();

        foreach ($sittings as $key => $sitting) {
            dump('Sitting num : ' . $key);

            $this->fileGenerator->genFullSittingPdf($sitting);
            $this->sittingZipGenerator->genZip($sitting);
        }
    }
}
