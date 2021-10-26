<?php

namespace App\Service\LegacyWs;

use App\Entity\Structure;
use App\Repository\UserRepository;

class ActorFinder
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function findByStructure(WsActor $actor, Structure $structure, string $username)
    {
        return $this->userRepository->findByFirstNameLastNameAndStructureOrUsername($actor->firstName, $actor->lastName, $structure, $username);
    }
}
