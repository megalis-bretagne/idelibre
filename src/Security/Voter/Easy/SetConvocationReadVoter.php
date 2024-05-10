<?php

namespace App\Security\Voter\Easy;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SetConvocationReadVoter extends Voter
{
    public function __construct(
        private readonly RequestStack          $requestStack,
        private readonly ConvocationRepository $convocationRepository
    ) {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['CONVOCATION_READ']) && ($subject instanceof Convocation);
        ;
    }


    /** @param Convocation $subject */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        if ($subject->getUser() !== $loggedInUser) {
            return false;
        }


        return $this->isAuthorizedSitting($subject->getSitting());
    }


    private function isAuthorizedSitting(Sitting $sitting): bool
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        if ($session->get('authorizedSittingId') === null) {
            return true;
        }
        return $session->get('authorizedSittingId') === $sitting->getId();
    }
}
