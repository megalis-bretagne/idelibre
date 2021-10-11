<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Structure;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Security\Core\Security;

class CurrentStructureExtension implements QueryCollectionExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @throws ReflectionException
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $class = new ReflectionClass($resourceClass);

        if ($class->hasProperty('structure')) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere("$alias.structure = :currentStructure")
                ->setParameter('currentStructure', $this->getStructure());
        }
    }

    private function getStructure(): Structure
    {
        return $this->security->getUser()->getStructure();
    }
}
