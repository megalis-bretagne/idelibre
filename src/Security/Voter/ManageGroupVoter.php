<?php

namespace App\Security\Voter;

use App\Entity\Group;
use App\Entity\Sitting;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\SittingRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageGroupVoter extends Voter
{


    public function __construct(private readonly Security $security)
    {

    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['MANAGE_GROUPS'])
            && ($subject instanceof Group);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPERADMIN')){
            return true;
        }

        return $this->isSameGroup($loggedInUser, $subject);
    }

    private function isSameGroup(User $loggedInUser, Group $group): bool
    {
        return $loggedInUser->getGroup()->getId() === $group->getId();
    }

}
