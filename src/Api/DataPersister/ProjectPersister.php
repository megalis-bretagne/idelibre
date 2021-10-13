<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Project;
use App\Service\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;

class ProjectPersister implements DataPersisterInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private ProjectManager $projectManager)
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
        $this->projectManager->deleteProjects([$data]);
    }
}
