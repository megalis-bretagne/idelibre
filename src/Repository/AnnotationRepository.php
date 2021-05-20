<?php

namespace App\Repository;

use App\Entity\Annotation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Annotation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annotation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annotation[]    findAll()
 * @method Annotation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annotation::class);
    }

}
