<?php

namespace App\Repository;

use App\Entity\ApiRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApiRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiRole[]    findAll()
 * @method ApiRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiRole::class);
    }

}
