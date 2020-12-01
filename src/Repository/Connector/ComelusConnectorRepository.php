<?php

namespace App\Repository\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class ComelusConnectorRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getConnector(Structure $structure): ComelusConnector
    {
        $qb = $this->em->createQueryBuilder();

        $q = $qb->select(['c'])
            ->from(ComelusConnector::class, 'c')
            ->andWhere('c.name = :name')
            ->setParameter('name', ComelusConnector::NAME)
            ->andWhere('c.structure = :structure')
            ->setParameter('structure', $structure)
            ->getQuery();

        return $q->getSingleResult();
    }

}
