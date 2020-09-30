<?php

namespace App\Security\Voter;

use App\Entity\Structure;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MyGroupVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['MY_GROUP'])
            && (
                $subject instanceof Structure || $subject instanceof User
            );
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdminGroupAndOwnSubject($user, $subject);
    }

    private function isSuperAdmin(User $user)
    {
        return in_array("ROLE_SUPERADMIN", $user->getRoles());
    }

    private function isAdminGroupAndOwnSubject(User $user, $subject):bool {
        if (!in_array("ROLE_GROUP_ADMIN", $user->getRoles())) {
            return false;
        }
        return $user->getGroup() === $subject->getGroup();
    }

}
