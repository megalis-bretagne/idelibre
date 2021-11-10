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

    // /**
    //  * @return DataControllerGdpr[] Returns an array of DataControllerGdpr objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataControllerGdpr
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
