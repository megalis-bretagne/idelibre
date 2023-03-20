<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte utilisateur est désactivé');
        }

        if($user->getStructure() && !$user->getStructure()->getIsActive()) {
            throw new CustomUserMessageAccountStatusException('La structure de votre utilisateur a été désactivée');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // interface
    }
}
