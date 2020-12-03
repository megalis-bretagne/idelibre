<?php

namespace App\Repository\Connector;

use App\Entity\Connector\ComelusConnector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComelusConnector|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComelusConnector|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComelusConnector[]    findAll()
 * @method ComelusConnector[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComelusConnectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComelusConnector::class);
    }

}
