<?php

namespace App\Tests;

trait FindEntityTrait
{
    public function getOneEntityBy($entityClass, array $criteria)
    {
        if (!$this->entityManager) {
            throw new \Exception('entityManager is not defined');
        }
        $repository = $this->entityManager->getRepository($entityClass);

        //dd($repository->findOneBy($criteria));
        return $repository->findOneBy($criteria);
    }
}
