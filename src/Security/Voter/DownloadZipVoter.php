<?php

namespace App\Security\Voter;

use App\Entity\Sitting;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\SittingRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DownloadZipVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['DOWNLOAD_ZIP'])
            && ($subject instanceof Sitting);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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

        if ($this->security->isGranted('ROLE_ACTOR')) {
            $convocation = $sitting->getConvocations()->map(fn ($convocation) => $convocation->getUser()->getId())->contains($user->getId());
            return !empty($convocation);
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
