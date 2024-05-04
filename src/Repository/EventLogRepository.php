<?php

namespace App\Repository;

use App\Entity\EventLog\EventLog;
use App\Entity\Structure;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventLog>
 *
 * @method EventLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventLog[]    findAll()
 * @method EventLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLog::class);
    }

    public function save(EventLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EventLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStructure(string $structureId, ?string $search = null)
    {
        $qb = $this->createQueryBuilder('el')
            ->where('el.structureId = :structureId')
            ->setParameter('structureId', $structureId);

        if (!empty($search)) {
            $qb->andWhere(
                'LOWER(el.authorName) like :search 
                OR LOWER(el.authorId) like :search 
                OR LOWER(el.action) like :search 
                OR LOWER(el.targetName) like :search
                OR LOWER(el.targetId) like :search'
            )
                ->setParameter('search', mb_strtolower("%{$search}%"));
        }

        return $qb;
    }


    public function findSittingsBefore(DateTimeInterface $before, Structure $structure)
    {
        return $this->createQueryBuilder('el')
            ->andWhere('el.structure = :structure')
            ->andWhere('el.date < :before')
            ->setParameter('structure', $structure)
            ->setParameter('before', $before)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string> $toRemoveEventLogIds
     */
    public function removeEventLogByIds(array $toRemoveEventLogIds): void
    {
        $this->createQueryBuilder('el')
            ->delete()
            ->where('el.id IN (:toRemoveEventLogIds)')
            ->setParameter('toRemoveEventLogIds', $toRemoveEventLogIds)
            ->getQuery()
            ->execute();
    }
}
