<?php

namespace App\Service\Configuration;

use App\Entity\Configuration;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class ConfigurationManager
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function createConfiguration(Structure $structure): void
    {
        $configuration = (new Configuration())
            ->setStructure($structure)
            ->setIsSharedAnnotation(true);

        $this->em->persist($configuration);

        $this->em->flush();
    }
}
