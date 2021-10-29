<?php

namespace App\Service\role;

use App\Entity\ApiRole;
use App\Entity\Role;
use App\Repository\ApiRoleRepository;
use App\Repository\RoleRepository;

class RoleManager
{
    public function __construct(private RoleRepository    $roleRepository,
                                private ApiRoleRepository $apiRoleRepository
    )
    {
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

    public function getApiStructureAdminRole(): ApiRole
    {
        return $this->apiRoleRepository->findOneBy(['name' => 'ApiStructureAdmin']);
    }
}
