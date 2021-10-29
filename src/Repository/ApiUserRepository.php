<?php

namespace App\Repository;

use App\Entity\ApiUser;
use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApiUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiUser[]    findAll()
 * @method ApiUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiUser::class);
    }

    public function findByStructure(Structure $structure): QueryBuilder
    {
        return $this->createQueryBuilder('au')
            ->andWhere('au.structure =:structure')
            ->setParameter('structure', $structure)
            ->orderBy('au.name', 'ASC');
    }
}
