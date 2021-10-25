<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[]
     */
    public function getProjectsWithAssociatedEntities(Sitting $sitting): iterable
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('p.annexes', 'a')
            ->leftJoin('p.theme', 't')
            ->leftJoin('p.reporter', 'r')
            ->addSelect('a')
            ->addSelect('r')
            ->addSelect('t')
            ->orderBy('p.rank', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Project[]
     */
    public function findNotInListProjects(array $projectIds, Sitting $sitting): iterable
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('p.annexes', 'a')
            ->leftJoin('p.file', 'pf')
            ->leftJoin('a.file', 'af')
            ->addSelect('a')
            ->addSelect('pf')
            ->addSelect('af');

        if (!empty($projectIds)) {
            $qb->andWhere('p.id not in (:projectIds)')
                ->setParameter('projectIds', $projectIds);
        }

        return $qb->getQuery()->getResult();
    }

    public function getProjectsBySitting(Sitting $sitting): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftjoin('p.annexes', 'a')
            ->addSelect('a')
            ->leftJoin('a.file', 'fa')
            ->addSelect('fa')
            ->leftJoin('p.file', 'fp')
            ->addSelect('fp')
            ->getQuery()
            ->getResult();
    }
}
