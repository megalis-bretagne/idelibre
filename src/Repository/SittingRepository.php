<?php

namespace App\Repository;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use DateTimeInterface;
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

    public function findByStructure(Structure $structure, ?string $searchTerm = null, ?string $status = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.structure =:structure')
            ->setParameter('structure', $structure);

        if ($searchTerm) {
            $qb->andWhere('LOWER(s.name) like :search')
                ->setParameter('search', mb_strtolower("%${searchTerm}%"));
        }

        if ($status) {
            $qb->andWhere('s.isArchived =:isArchived')
                ->setParameter('isArchived', !$this->isActive($status));
        }

        return $qb;
    }

    private function isActive(string $status): bool
    {
        if (Sitting::ARCHIVED === $status) {
            return false;
        }

        return true;
    }

    /**
     * @param Type[] $types
     */
    public function findWithTypesByStructure(Structure $structure, iterable $types, ?string $searchTerm = null, ?string $status = null): QueryBuilder
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

        if ($status) {
            $qb->andWhere('s.isArchived =:isArchived')
                ->setParameter('isArchived', !$this->isActive($status));
        }

        return $qb;
    }

    public function findActiveFromStructure(Structure $structure): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.structure =:structure')
            ->andWhere('s.isArchived = false')
            ->setParameter('structure', $structure)
            ->join('s.convocations', 'c')
            ->join('c.user', 'u')
            ->leftJoin('c.sentTimestamp', 'sent_timestamp')
            ->leftJoin('c.receivedTimestamp', 'received_timestamp')
            ->addSelect('c')
            ->addSelect('u')
            ->addSelect('sent_timestamp')
            ->addSelect('received_timestamp')
            ->orderBy('s.date', 'DESC');
    }

    public function findWithProjectsAndAnnexes(string $sittingId): ?Sitting
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id =:sittingId')
            ->setParameter('sittingId', $sittingId)
            ->leftjoin('s.convocations', 'c')
            ->leftJoin('s.projects', 'p')
            ->leftJoin('p.annexes', 'a')
            ->addSelect('c')
            ->addSelect('p')
            ->addSelect('a')
            ->orderBy('p.rank', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Sitting[]
     */
    public function findActiveSittingsAfterDate(Structure $structure, DateTimeInterface $after): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.structure =:structure')
            ->andWhere('s.isArchived = false')
            ->setParameter('structure', $structure)
            ->andWhere('s.date > :afterDate')
            ->setParameter('afterDate', $after)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
