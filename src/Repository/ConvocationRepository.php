<?php

namespace App\Repository;

use App\Entity\Convocation;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Convocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Convocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Convocation[]    findAll()
 * @method Convocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConvocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convocation::class);
    }

    /**
     * @return Convocation[]
     */
    public function getConvocationsBySitting(Sitting $sitting): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('c.actor', 'actor')
            ->addSelect('actor')
            ->orderBy('actor.lastName')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Convocation[]
     */
    public function getConvocationsBySittingAndActorIds(Sitting $sitting, array $actorIds): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->join('c.actor', 'actor')
            ->andWhere('actor.id in (:actorIds)')
            ->setParameter('actorIds', $actorIds)
            ->getQuery()
            ->getResult();
    }
}
