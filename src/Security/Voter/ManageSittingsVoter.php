<?php

namespace App\Security\Voter;

use App\Entity\Sitting;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\SittingRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageSittingsVoter extends Voter
{
    private Security $security;

    private SittingRepository $sittingRepository;

    public function __construct(Security $security, SittingRepository $sittingRepository)
    {
        $this->security = $security;
        $this->sittingRepository = $sittingRepository;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['MANAGE_SITTINGS'])
            && ($subject instanceof Sitting);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        if (!$this->isSameStructure($loggedInUser, $subject)) {
            return false;
        }

        return $this->isAuthorizedUser($loggedInUser, $subject);
    }

    private function isSameStructure(User $loggedInUser, Sitting $sitting): bool
    {
        return $loggedInUser->getStructure()->getId() === $sitting->getStructure()->getId();
    }

    private function isAuthorizedUser(User $user, Sitting $sitting): bool
    {
        if ($this->security->isGranted('ROLE_SECRETARY')) {
            return $this->isInAuthorisedType($user->getAuthorizedTypes(), $sitting->getType());
        }

        return $this->security->isGranted('ROLE_MANAGE_SITTINGS');
    }

    private function isInAuthorisedType(iterable $authorizedTypes, Type $type): bool
    {
        foreach ($authorizedTypes as $authorizedType) {
            if ($authorizedType->getId() === $type->getId()) {
                return true;
            }
        }

        return false;
    }
}
