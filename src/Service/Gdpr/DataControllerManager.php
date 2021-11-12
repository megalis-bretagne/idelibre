<?php

namespace App\Service\Gdpr;

use App\Entity\Gdpr\DataControllerGdpr;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class DataControllerManager
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function save(DataControllerGdpr $dataControllerGdpr, Structure $structure)
    {
        $dataControllerGdpr->setStructure($structure);
        $this->em->persist($dataControllerGdpr);
        $this->em->flush();
    }
}
