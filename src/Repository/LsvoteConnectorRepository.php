<?php

namespace App\Repository;

use App\Entity\Connector\LsvoteConnector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LsvoteConnector>
 *
 * @method LsvoteConnector|null find($id, $lockMode = null, $lockVersion = null)
 * @method LsvoteConnector|null findOneBy(array $criteria, array $orderBy = null)
 * @method LsvoteConnector[]    findAll()
 * @method LsvoteConnector[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LsvoteConnectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LsvoteConnector::class);
    }

    public function save(LsvoteConnector $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LsvoteConnector $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LsvoteConnector[] Returns an array of LsvoteConnector objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LsvoteConnector
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
