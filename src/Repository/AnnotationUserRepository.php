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


}
