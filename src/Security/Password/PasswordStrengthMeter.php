<?php

namespace App\Security\Password;

use App\Entity\User;
use App\Service\role\RoleManager;
use App\Service\RoleTrait;
use Libriciel\Password\Service\PasswordGeneratorAnssi;
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PasswordStrengthMeter
{
    use RoleTrait;
    public function __construct(
        private ParameterBagInterface $bag,
        private RoleManager $roleManager,
    ) {
    }

    public function checkPasswordEntropy(User $user, string $plainPassword)
    {
        if ($this->isSuperAdmin($user)) {
            $minimumEntropy = $this->bag->get('minimumEntropyForUserWithRoleHigh');
        } else {
            if (in_array($user->getRole(), $this->roleManager->getAllRolesAdmin())) {
                $minimumEntropy = $this->bag->get('minimumEntropyForUserWithRoleHigh');
            } else {
                $minimumEntropy = $user->getStructure()->getMinimumEntropy();
            }
        }

        return $this->checkEntropy($plainPassword, $minimumEntropy);
    }

    public function checkEntropy($plainPassword, $minimumEntropy): bool
    {
        $success = true;

        $pwdStrengthAnssi = new PasswordStrengthMeterAnssi();

        if ($pwdStrengthAnssi->entropy($plainPassword) < $minimumEntropy) {
            $success = false;
        }

        return $success;
    }

    public function generatePassword(): string
    {
        $passwordGeneratorAnssi = new PasswordGeneratorAnssi();

        do {
            $password = $passwordGeneratorAnssi->generate();
            $success = $this->checkEntropy($password, $this->bag->get('minimumEntropyForUserWithRoleHigh'));
        } while (false === $success);

        return $password;
    }
}
