<?php

namespace App\Security\Password;

use App\Repository\UserRepository;
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordUpdater
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly PasswordStrengthMeterAnssi $passwordStrengthMeter
    ) {
    }

    /**
     * @throws PasswordUpdaterException
     */
    public function replace(PasswordChange $passwordChange): bool
    {
        $user = $this->userRepository->find($passwordChange->userId);
        if (!$user) {
            throw new PasswordUpdaterException('BAD_USER_ID');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $passwordChange->plainCurrentPassword)) {
            throw new PasswordUpdaterException('INVALID_CURRENT_PASSWORD');
        }

        $passwordEntropy = $this->passwordStrengthMeter->entropy($passwordChange->plainNewPassword);
        $requiredEntropy = $user->getStructure()->getMinimumEntropy();
        if ($passwordEntropy < $requiredEntropy) {
            throw new PasswordUpdaterException('ENTROPY_TOO_LOW', $requiredEntropy, $passwordEntropy);
        }

        return true;
    }
}
