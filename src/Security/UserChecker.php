<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct()
    {
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (false === $user->getIsActive()) {
            throw new CustomUserMessageAccountStatusException('Your user account no longer exists.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
