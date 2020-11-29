<?php


namespace App\Service;

use Symfony\Component\Security\Core\User\UserInterface;

trait RoleTrait
{
    public function isSuperAdmin(UserInterface $user): bool
    {
        return in_array("ROLE_SUPERADMIN", $user->getRoles());
    }
}
