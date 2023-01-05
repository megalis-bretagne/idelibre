<?php

namespace App\Service\Jwt;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class JwtInvalidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository
    ) {
    }

    public function invalidate(User $user): void
    {
        $user->setJwtInvalidBefore(new DateTime('now'));

        $this->em->persist($user);
        $this->em->flush();
    }

    public function invalidateStructure(Structure $structure): void
    {
        $this->userRepository->updateUserJwtInvalidBefore($structure, new DateTime('now'));
    }
}
