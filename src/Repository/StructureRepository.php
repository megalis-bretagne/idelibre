<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\Group;
use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Structure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Structure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Structure[]    findAll()
 * @method Structure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Structure::class);
    }


    public function findAllQueryBuilder($search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.group', 'g')
            ->addSelect('g');

        if (!empty($search)) {
            $qb->andWhere('LOWER(s.name) like :search OR LOWER(g.name) like :search')
                ->setParameter('search', mb_strtolower("%${search}%"));
        }

        return $qb;
    }


    public function findByGroupQueryBuilder(Group $group, $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.group = :group')
            ->join('s.group', 'g')
            ->addSelect('g')
            ->setParameter('group', $group);

        if (!empty($search)) {
            $qb->andWhere('LOWER(s.name) like :search')
                ->setParameter('search', mb_strtolower("%${search}%"));
        }

        return $qb;
    }
}
