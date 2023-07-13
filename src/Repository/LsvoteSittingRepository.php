<?php

namespace App\Repository;

use App\Entity\LsvoteSitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LsvoteSitting>
 *
 * @method LsvoteSitting|null find($id, $lockMode = null, $lockVersion = null)
 * @method LsvoteSitting|null findOneBy(array $criteria, array $orderBy = null)
 * @method LsvoteSitting[]    findAll()
 * @method LsvoteSitting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LsvoteSittingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LsvoteSitting::class);
    }

    public function save(LsvoteSitting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LsvoteSitting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
