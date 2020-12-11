<?php

namespace App\Security\Voter;

use App\Entity\File;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DownloadFilesVoter extends Voter
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['DOWNLOAD_FILES'])
            && ($subject instanceof File);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        return $this->isSameStructure($loggedInUser, $subject);
    }

    private function isSameStructure(User $loggedInUser, File $subject)
    {
        return $loggedInUser->getStructure()->getId() === $subject->getStructure()->getId();
    }
}
