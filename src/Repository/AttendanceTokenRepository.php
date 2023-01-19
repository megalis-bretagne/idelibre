<?php

namespace App\Repository;

use App\Entity\AttendanceToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttendanceToken>
 *
 * @method AttendanceToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttendanceToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttendanceToken[]    findAll()
 * @method AttendanceToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendanceTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceToken::class);
    }

    public function save(AttendanceToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AttendanceToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
