<?php


namespace App\Service\role;

use App\Entity\Role;
use App\Repository\RoleRepository;

class RoleManager
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
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
}
