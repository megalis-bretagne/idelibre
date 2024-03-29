<?php

namespace App\Service\LegacyWs;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\Http403Exception;
use App\Security\Password\LegacyPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LegacyWsAuthentication
{
    public function __construct(
        private StructureRepository $structureRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LegacyPassword $legacyPassword
    ) {
    }

    public function getStructureFromLegacyConnection(string $legacyConnectionName): ?Structure
    {
        if (!$legacyConnectionName) {
            throw new Http403Exception('conn field is required');
        }

        $structure = $this->structureRepository->findOneBy(['legacyConnectionName' => $legacyConnectionName]);

        if (!$structure) {
            throw new Http403Exception('connection does not exist');
        }

        return $structure;
    }

    public function loginUser(Structure $structure, string $username, string $plainPassword): ?User
    {
        $user = $this->userRepository->findOneSecretaryInStructure($structure, $username);

        if (!$user) {
            throw new Http403Exception('Authentication error');
        }

        if (!$this->checkPassword($user, $plainPassword)) {
            throw new Http403Exception('Authentication error');
        }

        return $user;
    }

    private function checkPassword(User $user, string $plainPassword): bool
    {
        if ($this->passwordHasher->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        return $this->legacyPassword->checkAndUpdateCredentials($user, $plainPassword);
    }
}
