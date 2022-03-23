<?php

namespace App\Service\Statistic;

use App\Entity\Sitting;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use League\Csv\Writer;

class SittingByStructureStatisticCsvGenerator
{
    public function __construct(
        private StructureRepository $structureRepository,
        private SittingRepository $sittingRepository
    ) {
    }

    public function generate(int $month): string
    {
        $structures = $this->structureRepository->findAll();
        $csvPath = '/tmp/' . uniqid('csv_report');
        $writer = Writer::createFromPath($csvPath, 'w+');
        $writer->insertOne($this->getHeaders());

        foreach ($structures as $structure) {
            $sittings = $this->sittingRepository->findSittingsAfter(new \DateTime("- $month months"), $structure);
            $writer->insertOne($this->getFormattedSittingData($sittings, $structure->getName()));
        }

        return $csvPath;
    }

    private function getHeaders(): array
    {
        return ['Structure', 'nb seances', 'nb convocations', 'nb lues', 'nb non lues'];
    }

    /**
     * @param array<Sitting> $sittings
     */
    private function getFormattedSittingData(array $sittings, string $structureName): array
    {
        $countSitings = count($sittings);
        [$countSent, $countRead] = $this->countConvocationsByStatus($sittings);

        return [$structureName, $countSitings, $countSent, $countRead, $countSent - $countRead];
    }

    /**
     * @param array<Sitting> $sittings
     */
    private function countConvocationsByStatus(array $sittings): array
    {
        $countSent = 0;
        $countRead = 0;

        foreach ($sittings as $sitting) {
            foreach ($sitting->getConvocations() as $convocation) {
                if (!$convocation->getSentTimestamp()) {
                    continue;
                }
                ++$countSent;
                if ($convocation->getIsRead()) {
                    ++$countRead;
                }
            }
        }

        return [$countSent, $countRead];
    }
}
