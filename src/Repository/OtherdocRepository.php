<?php

namespace App\Repository;

use App\Entity\Otherdoc;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Otherdoc>
 *
 * @method Otherdoc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Otherdoc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Otherdoc[]    findAll()
 * @method Otherdoc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtherdocRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Otherdoc::class);
    }

    public function add(Otherdoc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Otherdoc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Otherdoc[]
     */
    public function getOtherdocsWithAssociatedEntities(Sitting $sitting): iterable
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->orderBy('o.rank', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Otherdoc[]
     */
    public function findNotInListOtherdocs(array $otherdocIds, Sitting $sitting): iterable
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('o.file', 'fo')
            ->addSelect('fo');

        if (!empty($otherdocIds)) {
            $qb->andWhere('o.id not in (:otherdocIds)')
                ->setParameter('otherdocIds', $otherdocIds);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Otherdoc[]
     */
    public function getOtherdocsBySitting(Sitting $sitting): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('o.file', 'fp')
            ->addSelect('fp')
            ->orderBy('o.rank', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
