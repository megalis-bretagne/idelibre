<?php

namespace App\Security;

use App\Entity\User;
use App\Service\role\RoleManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserLoginEntropy
{
    public function __construct(
        private readonly RoleManager $roleManager,
        private readonly ParameterBagInterface $bag,
    ) {
    }

    private function isUserWithRoleHigh(User $user): bool
    {
        if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
            return true;
        }

        if (in_array("ROLE_GROUP_ADMIN", $user->getRoles())) {
            return true;
        }

        if (in_array("ROLE_STRUCTURE_ADMIN", $user->getRoles())) {
            return true;
        }

        return false;
    }

    public function getEntropy(User $user): int
    {
        $isUserWithRoleHigh = $this->isUserWithRoleHigh($user);

        if (true === $isUserWithRoleHigh) {
            return $this->bag->get('minimumEntropyForUserWithRoleHigh');
        }

        if (in_array($user->getRole(), $this->roleManager->getAllRolesAdmin())) {
            $minimumEntropy = $this->bag->get('minimumEntropyForUserWithRoleHigh');
        } else {
            $minimumEntropy = $user->getStructure()->getMinimumEntropy();
        }

        return $minimumEntropy;
    }
}
