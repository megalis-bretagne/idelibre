<?php

namespace App\Security\Voter\Api;

use App\Entity\ApiUser;
use App\Entity\Structure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class MyStructureVoter extends Voter
{
    public function __construct(private Security $security)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['API_MY_STRUCTURE']) && ($subject instanceof Structure);
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var ApiUser $user */
        $apiUser = $token->getUser();

        if (!$apiUser instanceof ApiUser) {
            return false;
        }

        if (!$this->security->isGranted('ROLE_API_STRUCTURE_ADMIN')) {
            return false;
        }
        return $apiUser->getStructure()->getId() === $subject->getId();
    }


}
