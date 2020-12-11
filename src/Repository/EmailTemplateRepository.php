<?php

namespace App\Repository;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmailTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailTemplate[]    findAll()
 * @method EmailTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailTemplate::class);
    }

    public function findAllByStructure(Structure $structure): QueryBuilder
    {
        return $this->createQueryBuilder('et')
            ->andWhere('et.structure =:structure')
            ->setParameter('structure', $structure)
            ->leftJoin('et.type', 't')
            ->addSelect('t')
            ->orderBy('et.name');
    }

    public function findOneByType(Type $type): ?EmailTemplate
    {
        $qb = $this->createQueryBuilder('et')
            ->andWhere('et.type =:type')
            ->setParameter('type', $type);

        $templates = $qb->getQuery()->getResult();
        if (!empty($templates)) {
            return $templates[0];
        }

        return null;
    }
}
