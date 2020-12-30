<?php

namespace App\Repository;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sitting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sitting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sitting[]    findAll()
 * @method Sitting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SittingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sitting::class);
    }

    public function findByStructure(Structure $structure, ?string $searchTerm = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.structure =:structure')
            ->setParameter('structure', $structure);

        if ($searchTerm) {
            $qb->andWhere('LOWER(s.name) like :search')
                ->setParameter('search', mb_strtolower("%${searchTerm}%"));
        }

        return $qb;
    }

    /**
     * @param Type[] $types
     */
    public function findWithTypesByStructure(Structure $structure, iterable $types, ?string $searchTerm = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.structure =:structure')
            ->setParameter('structure', $structure)
            ->join('s.type', 't')
            ->andWhere('t in (:types)')
            ->setParameter('types', $types);

        if ($searchTerm) {
            $qb->andWhere('LOWER(s.name) like :search')
                ->setParameter('search', mb_strtolower("%${searchTerm}%"));
        }

        return $qb;
    }
}
