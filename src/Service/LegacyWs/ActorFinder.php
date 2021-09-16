<?php

namespace App\Service\LegacyWs;

use App\Entity\Structure;
use App\Repository\UserRepository;

class ActorFinder
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findByStructure(WsActor $actor, Structure $structure, string $username)
    {
        return $this->userRepository->findByFirstNameLastNameAndStructureOrUsername($actor->firstName, $actor->lastName, $structure, $username);
    }
}
