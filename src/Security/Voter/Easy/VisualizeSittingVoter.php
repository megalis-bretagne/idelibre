<?php

namespace App\Security\Voter\Easy;

use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class VisualizeSittingVoter extends Voter
{
    public function __construct(
        private readonly RequestStack          $requestStack,
        private readonly ConvocationRepository $convocationRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['VISUALIZE_SITTING']) && ($subject instanceof Sitting);;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        if (!$this->isConvocationToSitting($subject, $loggedInUser)) {
            return false;
        }

        return $this->isAuthorizedSitting($subject);
    }


    private function isConvocationToSitting($sitting, $user): bool
    {
        $convocation = $this->convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $user]);
        if (!$convocation) {
            return false;
        }

        return ($convocation->getIsActive() && $convocation->getSentTimestamp());

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
