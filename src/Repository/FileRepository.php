<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function findAllCachedExpired(): iterable
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.cachedAt <= :dateExpired')
            ->setParameter('dateExpired', date('Y-m-d H:m:s'))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByPath(string $path): File
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.path = :path')
            ->setParameter('path' , $path)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function deleteCachedFiles(): void
    {
        $this->createQueryBuilder('f')
            ->update(File::class, 'f')
            ->set('f.cachedAt', ':value')
            ->setParameter('value', null)
            ->getQuery()
            ->execute()
        ;
    }

    public function getAllFiles(): iterable
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.createdAt')
        ;

        return $qb->getQuery()->getResult();
    }
}
