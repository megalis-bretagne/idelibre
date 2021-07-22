<?php

namespace App\Repository;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Type|null find($id, $lockMode = null, $lockVersion = null)
 * @method Type|null findOneBy(array $criteria, array $orderBy = null)
 * @method Type[]    findAll()
 * @method Type[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function findByStructure(Structure $structure, ?string $searchTerm = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.structure =:structure')
            ->setParameter('structure', $structure)
            ->addOrderBy('t.name');

        if (!empty($searchTerm)) {
            $qb->andWhere('LOWER(t.name) like :search')
                ->setParameter('search', mb_strtolower("%${searchTerm}%"));
        }

        return $qb;
    }

    public function findNotAssociatedWithOtherTemplateByStructure(Structure $structure, ?EmailTemplate $emailTemplate): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.structure =:structure')
            ->setParameter('structure', $structure)
            ->leftJoin('t.emailTemplate', 'et')
            ->andWhere('et is null or et =:emailTemplate')
            ->setParameter('emailTemplate', $emailTemplate);
    }

    public function exists(string $typeName, Structure $structure): bool
    {
        return $this->count(['name' => $typeName, 'structure' => $structure]) > 0;
    }
}
