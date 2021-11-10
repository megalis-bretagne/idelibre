<?php

namespace App\Repository;

use App\Entity\Gdpr\GdprHosting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdprHosting|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdprHosting|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdprHosting[]    findAll()
 * @method GdprHosting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdprHostingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdprHosting::class);
    }
}
