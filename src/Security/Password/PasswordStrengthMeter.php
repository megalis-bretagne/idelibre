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
        private readonly ParameterBagInterface $bag,
        private readonly RoleManager $roleManager,
        private readonly PasswordStrengthMeterAnssi $passwordStrengthMeter,
        private readonly PasswordGeneratorAnssi $passwordGeneratorAnssi
    ) {
    }

    public function getPasswordEntropy($plainPassword): string
    {
        return $this->passwordStrengthMeter->entropy($plainPassword);
    }

    public function checkPasswordEntropy(User $user, string $plainPassword): bool
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

        if ($this->passwordStrengthMeter->entropy($plainPassword) < $minimumEntropy) {
            $success = false;
        }

        return $success;
    }

    public function generatePassword(): string
    {
        do {
            $password = $this->passwordGeneratorAnssi->generate();
            $success = $this->checkEntropy($password, $this->bag->get('minimumEntropyForUserWithRoleHigh'));
        } while (false === $success);

        return $password;
    }
}
