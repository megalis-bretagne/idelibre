<?php

namespace App\Repository;

use App\Entity\Convocation;
use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
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
            $qb->andWhere(
                'LOWER(u.lastName) like :search 
            OR LOWER(u.username) like :search 
            OR LOWER(u.firstName) like :search 
            OR LOWER(r.prettyName) like :search
            OR LOWER(CONCAT(u.firstName, \' \', u.lastName )) like :search'
            )
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

    public function findActorsByStructure(Structure $structure): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:actor')
            ->setParameter('actor', Role::NAME_ROLE_ACTOR)
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->orderBy('u.lastName', 'ASC');
    }

    public function findOneSecretaryInStructure(Structure $structure, string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:secretary')
            ->setParameter('secretary', Role::NAME_ROLE_SECRETARY)
            ->andWhere(' u.username =:username')
            ->setParameter('username', $username)
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string[] $userIds
     */
    public function deleteActorsByStructure(Structure $structure, array $userIds)
    {
        $qb = $this->createQueryBuilder('u')
            ->delete()
            ->where('u.id in (:userIds)')
            ->setParameter('userIds', $userIds)
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->getQuery();

        return $qb->execute();
    }

    public function findGuestsByStructure($structure): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:actor')
            ->setParameter('actor', 'Guest')
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->orderBy('u.lastName', 'ASC');
    }

    public function findInvitableEmployeesByStructure($structure): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:employee or r.name=:secretary or r.name=:administrator')
            ->setParameter('employee', Role::NAME_ROLE_EMPLOYEE)
            ->setParameter('secretary', Role::NAME_ROLE_SECRETARY)
            ->setParameter('administrator', Role::NAME_ROLE_STRUCTURE_ADMINISTRATOR)
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->orderBy('u.lastName', 'ASC');
    }

    public function findActorsInSitting(Sitting $sitting): QueryBuilder
    {
        return $this->findWithRoleInSitting($sitting, [Role::NAME_ROLE_ACTOR]);
    }

    public function findInvitableEmployeesInSitting(Sitting $sitting): QueryBuilder
    {
        return $this->findWithRoleInSitting($sitting, Role::INVITABLE_EMPLOYEE);
    }

    public function findGuestsInSitting(Sitting $sitting): QueryBuilder
    {
        return $this->findWithRoleInSitting($sitting, [Role::NAME_ROLE_GUEST]);
    }

    /**
     * @param string[] $roleNames
     */
    public function findWithRoleInSitting(Sitting $sitting, array $roleNames): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name in (:roleNames)')
            ->setParameter('roleNames', $roleNames)
            ->join(Convocation::class, 'c', Join::WITH, 'c.user = u')
            ->andWhere('c.sitting =:sitting')
            ->setParameter('sitting', $sitting);
    }

    public function findActorsNotInSitting(Sitting $sitting, Structure $structure): QueryBuilder
    {
        return $this->findWithRoleNotInSitting($sitting, $structure, [Role::NAME_ROLE_ACTOR]);
    }

    public function findInvitableEmployeesNotInSitting(Sitting $sitting, Structure $structure): QueryBuilder
    {
        return $this->findWithRoleNotInSitting($sitting, $structure, Role::INVITABLE_EMPLOYEE);
    }

    public function findGuestNotInSitting(Sitting $sitting, Structure $structure): QueryBuilder
    {
        return $this->findWithRoleNotInSitting($sitting, $structure, [Role::NAME_ROLE_GUEST]);
    }

    private function findWithRoleNotInSitting(Sitting $sitting, Structure $structure, array $roleNames): QueryBuilder
    {
        $actorsInSitting = $this->findWithRoleInSitting($sitting, $roleNames)->getQuery()->getResult();

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name in (:roleNames)')
            ->setParameter('roleNames', $roleNames)
            ->andWhere('u.structure =:structure')
            ->andWhere('u.isActive = true')
            ->setParameter('structure', $structure);

        if (!empty($actorsInSitting)) {
            $qb->andWhere('u not in (:alreadyIn)')
                ->setParameter('alreadyIn', $actorsInSitting);
        }

        return $qb;
    }

    public function findActorIdsConvocationSent(Sitting $sitting): array
    {
        return $this->findWithRolesIdsConvocationSent($sitting, [Role::NAME_ROLE_ACTOR]);
    }

    public function findInvitableEmployeesIdsConvocationSent(Sitting $sitting): array
    {
        return $this->findWithRolesIdsConvocationSent($sitting, Role::INVITABLE_EMPLOYEE);
    }

    public function findGuestsIdsConvocationSent(Sitting $sitting): array
    {
        return $this->findWithRolesIdsConvocationSent($sitting, [Role::NAME_ROLE_GUEST]);
    }

    private function findWithRolesIdsConvocationSent(Sitting $sitting, array $roleNames): array
    {
        $qb = $this->findWithRoleInSitting($sitting, $roleNames)
            ->andWhere('c.sentTimestamp is not null')
            ->select('u.id');

        $associatedArrayIds = $qb->getQuery()->getScalarResult();

        return array_map(fn ($el) => $el['id'], $associatedArrayIds);
    }

    public function findSecretariesByStructure($structure): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name =:secretary')
            ->setParameter('secretary', 'Secretary')
            ->andWhere('u.structure = :structure')
            ->setParameter('structure', $structure)
            ->orderBy('u.lastName', 'ASC');
    }

    public function getAssociatedActorsWithType(?Type $type): ?array
    {
        return $this->getAssociatedUsersRoleWithType([Role::NAME_ROLE_ACTOR], $type);
    }

    public function getAssociatedInvitableEmployeesWithType(?Type $type): ?array
    {
        return $this->getAssociatedUsersRoleWithType(Role::INVITABLE_EMPLOYEE, $type);
    }

    public function getAssociatedGuestWithType(?Type $type): ?array
    {
        return $this->getAssociatedUsersRoleWithType([Role::NAME_ROLE_GUEST], $type);
    }

    /**
     * @param string[] $roleNames
     *
     * @return User[]|null
     */
    private function getAssociatedUsersRoleWithType(array $roleNames, ?Type $type): ?array
    {
        if (!$type) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->join('u.associatedTypes', 't')
            ->andWhere(' t =:type')
            ->setParameter('type', $type)
            ->leftJoin('u.role', 'r')
            ->andWhere(' r.name in (:roleCondition)')
            ->andWhere('u.isActive = true')
            ->setParameter('roleCondition', $roleNames)
            ->getQuery()
            ->getResult();
    }

    public function findByFirstNameLastNameAndStructureOrUsername(string $firstName, string $lastName, Structure $structure, string $username): ?User
    {
        $users = $this->createQueryBuilder('u')
            ->andWhere('u.structure =:structure')
            ->setParameter('structure', $structure)
            ->andWhere('(TRIM(LOWER(u.firstName)) = :firstName and TRIM(LOWER(u.lastName)) = :lastName) OR (u.username = :username)')
            ->setParameter('firstName', mb_strtolower(trim($firstName)))
            ->setParameter('lastName', mb_strtolower(trim($lastName)))
            ->setParameter('username', $username)
            ->getQuery()
            ->getResult();

        if (!count($users)) {
            return null;
        }

        return $users[0];
    }

    public function findUsersByIds(Structure $structure, array $userIds): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.structure =:structure')
            ->setParameter('structure', $structure)
            ->andWhere('u.id in (:userIds)')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();
    }
}
