<?php

namespace App\Service\Statistic;

use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use League\Csv\Writer;

class RoleByStructureStatisticCsvGenerator
{
    public function __construct(
        private StructureRepository $structureRepository,
        private UserRepository $userRepository
    )
    {
    }

    public function generate(): string
    {
        $structures = $this->structureRepository->findAll();
        $csvPath = '/tmp/' . uniqid('csv_report');
        $writer = Writer::createFromPath($csvPath, 'w+');

        foreach ($structures as $structure) {
            $roleCount = $this->userRepository->countByRole($structure);
            $writer->insertOne($this->getFormattedRoleData($roleCount, $structure->getName()));
        }

        return $csvPath;
    }

    private function getFormattedRoleData(array $roleCountArray, $structureName): array
    {
        $formatted = [$structureName];

        foreach ($roleCountArray as $roleCount) {
            $formatted[] = $roleCount['prettyName'];
            $formatted[] = $roleCount['count'];
        }

        return $formatted;
    }
}
