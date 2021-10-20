<?php

namespace App\Security\Voter;

use App\Entity\Structure;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MyStructure extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['MY_STRUCTURE'])  && ($subject instanceof Structure);
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }



        if ($this->isSuperAdmin($user) || $this->isAdminGroupAndOwnStructure($user, $subject)) {
            return true;
        }

        return $user->getStructure()->getId() === $subject->getId();
    }

    private function isSuperAdmin(User $user)
    {
        return in_array('ROLE_SUPERADMIN', $user->getRoles());
    }

    private function isAdminGroupAndOwnStructure(User $user,Structure $structure): bool
    {
        if (!in_array('ROLE_GROUP_ADMIN', $user->getRoles())) {
            return false;
        }

        return $user->getGroup() === $structure->getGroup();
    }
}
