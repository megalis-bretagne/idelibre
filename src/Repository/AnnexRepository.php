<?php

namespace App\Repository;

use App\Entity\Annex;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Annex|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annex|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annex[]    findAll()
 * @method Annex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annex::class);
    }

    /**
     * @return Annex[]
     */
    public function findNotInListAnnexes(array $annexeIds, Sitting $sitting): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.project', 'p')
            ->andWhere('p.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('a.file', 'f')
            ->addSelect('f');

        if (!empty($annexeIds)) {
            $qb->andWhere('a.id not in (:annexeIds)')
                ->setParameter('annexeIds', $annexeIds);
        }

        return $qb->getQuery()->getResult();
    }
}
