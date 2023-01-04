<?php

namespace App\Security\Voter;

use App\Entity\ApiUser;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageApiUsersVoter extends Voter
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['MANAGE_API_USERS'])
            && ($subject instanceof ApiUser);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        if ($this->isSameStructure($loggedInUser, $subject)) {
            return $this->security->isGranted('ROLE_MANAGE_API_USER');
        }

        return false;
    }

    private function isSameStructure(User $loggedInUser, ApiUser $subject)
    {
        return $loggedInUser->getStructure()->getId() === $subject->getStructure()->getId();
    }
}
