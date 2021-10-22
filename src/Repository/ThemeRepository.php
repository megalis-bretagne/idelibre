<?php

namespace App\Repository;

use App\Entity\Structure;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Theme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Theme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Theme[]    findAll()
 * @method Theme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeRepository extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new  ClassMetadata(Theme::class));
    }

    public function findChildrenFromStructure(Structure $structure): QueryBuilder
    {
        $rootNode = $this->findOneBy(['name' => 'ROOT', 'structure' => $structure]);

        return $this->createQueryBuilder('t')
            // On devrait utiliser cet order by et mettre dans l'ordre au moment de l'ajout ->orderBy('t.lft', 'ASC')
            ->orderBy('t.fullName', 'ASC')
            ->andWhere('t.lft >:rootNodeLft')
            ->andWhere('t.rgt <:rootNodeRgt')
            ->andWhere('t.root =:rootNode')
            ->setParameter('rootNodeLft', $rootNode->getLft())
            ->setParameter('rootNodeRgt', $rootNode->getRgt())
            ->setParameter('rootNode', $rootNode);
    }

    public function findRootNodeByStructure(Structure $structure): Theme
    {
        return $this->findOneBy(['name' => 'ROOT', 'structure' => $structure]);
    }




}
