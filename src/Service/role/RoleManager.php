<?php

namespace App\Service\role;

use App\Entity\Role;
use App\Repository\RoleRepository;

class RoleManager
{
    public function __construct(private RoleRepository $roleRepository)
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
}
