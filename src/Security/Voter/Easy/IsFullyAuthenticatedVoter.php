<?php

namespace App\Security\Voter\Easy;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class IsFullyAuthenticatedVoter extends Voter
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['fully_authenticated']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        $session = $this->requestStack->getCurrentRequest()->getSession();
        return $session->get('authorizedSittingId') === null;
    }
}
