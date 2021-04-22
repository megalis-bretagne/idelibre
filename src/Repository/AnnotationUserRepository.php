<?php

namespace App\Repository;

use App\Entity\AnnotationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnnotationUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnnotationUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnnotationUser[]    findAll()
 * @method AnnotationUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnotationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnnotationUser::class);
    }

    // /**
    //  * @return AnnotationUser[] Returns an array of AnnotationUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AnnotationUser
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
