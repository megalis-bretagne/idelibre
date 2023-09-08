<?php

namespace App\Service\role;

use App\Entity\ApiRole;
use App\Entity\Role;
use App\Repository\ApiRoleRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoleManager
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly ApiRoleRepository $apiRoleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getSuperAdminRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'SuperAdmin']);
    }

    public function getGroupAdminRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'GroupAdmin']);
    }

    public function getActorRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Actor']);
    }

    public function getDeputyRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Deputy']);
    }

    public function getStructureAdminRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Admin']);
    }

    public function getSecretaryRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Secretary']);
    }

    public function getEmployeeRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Employee']);
    }

    public function getGuestRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Guest']);
    }
    public function getAdminRole(): Role
    {
        return $this->roleRepository->findOneBy(['name' => 'Admin']);
    }
    public function getApiStructureAdminRole(): ApiRole
    {
        return $this->apiRoleRepository->findOneBy(['name' => 'ApiStructureAdmin']);
    }

    public function getAllRolesAdmin(): array
    {
        return $this->roleRepository->findBy([
            'name' => [
                'GroupAdmin',
                'Admin',
            ],
        ]);
    }

    public function createNotAdminRole(string $roleName, string $prettyName, $inStructureRole):void
    {
        $role = (new Role())
            ->setIsInStructureRole(true)
            ->setName($roleName)
            ->setPrettyName($prettyName)
            ->setIsInStructureRole($inStructureRole)
            ->setComposites([strtoupper("ROLE_".$roleName)])
        ;
        $this->entityManager->persist($role);
        $this->entityManager->flush();
    }
}
