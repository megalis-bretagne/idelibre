<?php

namespace App\Repository;

use App\Entity\Gdpr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gdpr|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gdpr|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gdpr[]    findAll()
 * @method Gdpr[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdprRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gdpr::class);
    }

    // /**
    //  * @return Gdpr[] Returns an array of Gdpr objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Gdpr
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
