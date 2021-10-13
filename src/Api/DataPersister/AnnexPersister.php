<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Project;
use App\Service\Annex\AnnexManager;
use Doctrine\ORM\EntityManagerInterface;

class AnnexPersister implements DataPersisterInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private AnnexManager $annexManager)
    {
    }

    public function supports($data): bool
    {
        return $data instanceof Project;
    }

    public function persist($data)
    {
        return $data;
    }

    public function remove($data)
    {
        $this->annexManager->deleteAnnexes([$data]);
    }
}
