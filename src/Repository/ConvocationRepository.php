<?php

namespace App\Repository;

use App\Entity\Convocation;
use App\Entity\Role;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Convocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Convocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Convocation[]    findAll()
 * @method Convocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConvocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convocation::class);
    }

    /**
     * @return Convocation[]
     */
    public function getActorConvocationsBySitting(Sitting $sitting): array
    {
        return $this->getWithRolesConvocationsBySitting($sitting, [Role::NAME_ROLE_ACTOR]);
    }

    /**
     * @return Convocation[]
     */
    public function getInvitableEmployeeConvocationsBySitting(Sitting $sitting): array
    {
        return $this->getWithRolesConvocationsBySitting($sitting, Role::INVITABLE_EMPLOYEE);
    }

    /**
     * @return Convocation[]
     */
    public function getGuestConvocationsBySitting(Sitting $sitting): array
    {
        return $this->getWithRolesConvocationsBySitting($sitting, [Role::NAME_ROLE_GUEST]);
    }

    /**
     * @return Convocation[]
     */
    private function getWithRolesConvocationsBySitting(Sitting $sitting, array $roleNames): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->leftJoin('c.user', 'user')
            ->addSelect('user')
            ->innerJoin('user.role', 'r')
            ->andWhere('r.name in (:roleNames)')
            ->setParameter('roleNames', $roleNames)
            ->orderBy('user.lastName')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Convocation[]
     */
    public function getConvocationsBySittingAndActorIds(Sitting $sitting, array $userIds): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.sitting = :sitting')
            ->setParameter('sitting', $sitting)
            ->join('c.user', 'user')
            ->andWhere('user.id in (:userIds)')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string[] $convocationIds
     * @return array
     */
    public function getConvocationsWithUser(array $convocationIds): array {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id in (:convocationIds)')
            ->setParameter('convocationIds', $convocationIds)
            ->join('c.user', 'user')
            ->addSelect('user')
            ->getQuery()
            ->getResult();
    }

}
