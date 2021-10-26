<?php

namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\User;
use App\Repository\UserRepository;

class ActorManager
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * @return User[]
     */
    public function getActorsBySitting(Sitting $sitting): array
    {
        return $this->userRepository->findActorsInSitting($sitting)->getQuery()->getResult();
    }
}
