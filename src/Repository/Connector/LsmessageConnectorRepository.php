<?php

namespace App\Repository\Connector;

use App\Entity\Connector\LsmessageConnector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LsmessageConnector|null find($id, $lockMode = null, $lockVersion = null)
 * @method LsmessageConnector|null findOneBy(array $criteria, array $orderBy = null)
 * @method LsmessageConnector[]    findAll()
 * @method LsmessageConnector[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LsmessageConnectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LsmessageConnector::class);
    }

}
