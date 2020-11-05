<?php

namespace App\Tests;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Type;

trait FindEntityTrait
{
    public function getOneEntityBy($entityClass, array $criteria)
    {
        if (!$this->entityManager) {
            throw new \Exception('entityManager is not defined');
        }
        $repository = $this->entityManager->getRepository($entityClass);

        return $repository->findOneBy($criteria);
    }


    public function getOneSittingBy(array $criteria): Sitting
    {
        return $this->getOneEntityBy(Sitting::class, $criteria);
    }


    public function getOneProjectBy(array $criteria): Project
    {
        return $this->getOneEntityBy(Project::class, $criteria);
    }

    public function getOneAnnexBy(array $criteria): Annex
    {
        return $this->getOneEntityBy(Annex::class, $criteria);
    }

    public function getOneTypeBy(array $criteria): Type
    {
        return $this->getOneEntityBy(Type::class, $criteria);
    }
}
