<?php

namespace App\Service\User;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ImpersonateStructure
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserRepository $userRepository
    ) {
    }

    public function logInStructure(Structure $structure): bool
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $user->setStructure($structure);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }

    public function logoutStructure(): bool
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $user->setStructure(null);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }

    public function logoutEverySuperAdmin(Structure $structure): bool
    {
        $users = $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure)->getResult();
        foreach ($users as $user) {
            $user->setStructure(null);
            $this->em->persist($user);
        }
        $this->em->flush();

        return true;
    }
}
