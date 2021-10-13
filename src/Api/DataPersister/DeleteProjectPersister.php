<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class DeleteProjectPersister implements DataPersisterInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
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
        dump('removeProject');
        dump($data);
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
