<?php

namespace App\Security\Voter;

use App\Entity\EmailTemplate;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageEmailTemplatesVoter extends Voter
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['MANAGE_EMAIL_TEMPLATES'])
            && ($subject instanceof EmailTemplate);
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
            return $this->security->isGranted('ROLE_MANAGE_EMAIL_TEMPLATES');
        }

        return false;
    }

    private function isSameStructure(User $loggedInUser, EmailTemplate $subject)
    {
        return $loggedInUser->getStructure()->getId() === $subject->getStructure()->getId();
    }
}
