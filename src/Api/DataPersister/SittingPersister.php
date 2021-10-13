<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Sitting;
use App\Service\Seance\SittingManager;

class SittingPersister implements DataPersisterInterface
{
    public function __construct(private SittingManager $sittingManager)
    {
    }

    public function supports($data): bool
    {
        return $data instanceof Sitting;
    }

    public function persist($data)
    {
        return $data;
    }

    public function remove($data)
    {
        $this->sittingManager->delete($data);
    }
}
