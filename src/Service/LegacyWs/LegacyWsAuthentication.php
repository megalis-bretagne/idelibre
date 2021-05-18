<?php

namespace App\Service\LegacyWs;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LegacyWsAuthentication
{
    private StructureRepository $structureRepository;
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $passwordEncoder;
    private LegacyPassword $legacyPassword;

    public function __construct(
        StructureRepository $structureRepository,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        LegacyPassword $legacyPassword
    ) {
        $this->structureRepository = $structureRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->legacyPassword = $legacyPassword;
    }

    public function getStructureFromLegacyConnection(string $legacyConnectionName): ?Structure
    {
        if (!$legacyConnectionName) {
            return null;
        }

        return $this->structureRepository->findOneBy(['legacyConnectionName' => $legacyConnectionName]);
    }

    public function loginUser(Structure $structure, string $username, string $plainPassword): ?User
    {
        $user = $this->userRepository->findOneSecretaryInStructure($structure, $username);

        if (!$user) {
            return null;
        }

        if (!$this->checkPassword($user, $plainPassword)) {
            return null;
        }

        return $user;
    }

    private function checkPassword(User $user, string $plainPassword): bool
    {
        if ($this->passwordEncoder->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        return $this->legacyPassword->checkAndUpdateCredentials($user, $plainPassword);
    }
}
