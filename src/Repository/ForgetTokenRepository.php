<?php

namespace App\Repository;

use App\Entity\ForgetToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ForgetToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForgetToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForgetToken[]    findAll()
 * @method ForgetToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForgetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForgetToken::class);
    }
}
