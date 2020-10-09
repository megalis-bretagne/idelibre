<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function findByStructure(Structure $structure, ?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.structure =:structure')
            ->setParameter('structure', $structure)
            ->leftJoin('u.role', 'r')
            ->andWhere('
            (r.name !=:superAdmin AND r.name !=:groupAdmin )
            OR 
            (r.name is null)')
            ->setParameter('superAdmin', 'SuperAdmin')
            ->setParameter('groupAdmin', 'GroupAdmin')
            ->addSelect('r');


        if (!empty($search)) {
            $qb->andWhere('LOWER(u.lastName) like :search OR LOWER(u.username) like :search')
                ->setParameter('search', mb_strtolower("%${search}%"));
        }
        return $qb;

    }


    public function findSuperAdminAndGroupAdmin(?Group $group, ?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:superAdmin OR r.name=:groupAdmin')
            ->setParameter('superAdmin', 'SuperAdmin')
            ->setParameter('groupAdmin', 'GroupAdmin')
            ->leftJoin('u.group', 'g')
            ->addSelect('g');

        if ($group) {
            $qb->andWhere('u.group = :group')
                ->setParameter('group', $group);
        }

        if (!empty($search)) {
            $qb->andWhere('LOWER(u.lastName) like :search OR LOWER(u.email) like :search')
                ->setParameter('search', mb_strtolower("%${search}%"));
        }

        return $qb;
    }


    public function findSuperAdminAndGroupAdminInStructure(Structure $structure): Query
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:superAdmin OR r.name=:groupAdmin')
            ->setParameter('superAdmin', 'SuperAdmin')
            ->setParameter('groupAdmin', 'GroupAdmin')
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure);

        return $qb->getQuery();
    }

    public function findActorByStructure($structure): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:actor')
            ->setParameter('actor', 'Actor')
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure);
    }
}
