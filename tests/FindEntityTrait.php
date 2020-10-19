<?php

namespace App\Tests;

use App\Entity\Sitting;

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
}
