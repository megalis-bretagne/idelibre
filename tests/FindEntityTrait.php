<?php

namespace App\Tests;

use App\Entity\Annex;
use App\Entity\Convocation;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;

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

    public function getOneSittingBy(array $criteria): ?Sitting
    {
        return $this->getOneEntityBy(Sitting::class, $criteria);
    }

    public function getOneProjectBy(array $criteria): ?Project
    {
        return $this->getOneEntityBy(Project::class, $criteria);
    }

    public function getOneAnnexBy(array $criteria): ?Annex
    {
        return $this->getOneEntityBy(Annex::class, $criteria);
    }

    public function getOneTypeBy(array $criteria): ?Type
    {
        return $this->getOneEntityBy(Type::class, $criteria);
    }

    public function getOneUserBy(array $criteria): ?User
    {
        return $this->getOneEntityBy(User::class, $criteria);
    }

    public function getOneConvocationBy(array $criteria): ?Convocation
    {
        return $this->getOneEntityBy(Convocation::class, $criteria);
    }

    public function getOneStructureBy(array $criteria): ?Structure
    {
        return $this->getOneEntityBy(Structure::class, $criteria);
    }
}
