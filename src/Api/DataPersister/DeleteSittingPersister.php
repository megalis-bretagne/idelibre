<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Sitting;
use App\Service\Seance\SittingManager;
use Doctrine\ORM\EntityManagerInterface;

class DeleteSittingPersister implements DataPersisterInterface
{

    public function __construct(private EntityManagerInterface $entityManager, private SittingManager $sittingManager)
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
        //$this->entityManager->remove($data);
        //$this->entityManager->flush();
        $this->sittingManager->delete($data);
    }
}
