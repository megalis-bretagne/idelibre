<?php

namespace App\Repository\Connector;

use App\Entity\Connector\LsmessageConnector;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class LsmessageConnectorRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getConnector(Structure $structure): LsmessageConnector
    {
        $qb = $this->em->createQueryBuilder();

        $q = $qb->select(['c'])
            ->from(LsmessageConnector::class, 'c')
            ->andWhere('c.name = :name')
            ->setParameter('name', LsmessageConnector::NAME)
            ->andWhere('c.structure = :structure')
            ->setParameter('structure', $structure)
            ->getQuery();

        return $q->getSingleResult();
    }

}
