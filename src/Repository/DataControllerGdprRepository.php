<?php

namespace App\Repository;

use App\Entity\Gdpr\DataControllerGdpr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataControllerGdpr|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataControllerGdpr|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataControllerGdpr[]    findAll()
 * @method DataControllerGdpr[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataControllerGdprRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataControllerGdpr::class);
    }
}
