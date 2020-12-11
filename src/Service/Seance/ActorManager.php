<?php

namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\User;
use App\Repository\UserRepository;

class ActorManager
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return User[]
     */
    public function getActorsBySitting(Sitting $sitting): array
    {
        return $this->userRepository->findActorsInSitting($sitting, $sitting->getStructure())->getQuery()->getResult();
    }
}
