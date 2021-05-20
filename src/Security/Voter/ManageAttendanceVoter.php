<?php

namespace App\Security\Voter;

use App\Entity\Convocation;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageAttendanceVoter extends Voter
{
    private Security $security;

    private ConvocationRepository $convocationRepository;

    public function __construct(Security $security, ConvocationRepository $convocationRepository)
    {
        $this->security = $security;
        $this->convocationRepository = $convocationRepository;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['MANAGE_ATTENDANCE'])
            && ($subject instanceof Request);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$loggedInUser instanceof UserInterface) {
            return false;
        }

        /**
         * [["convocationId" => "e995df60-45f1-4903-b5c6-fdc94a66604d"
         *  "attendance" => "absent"
         *  "deputy" => null], [] ...].
         */
        $dataArray = $subject->toArray();

        foreach ($dataArray as $data) {
            $convocation = $this->convocationRepository->find($data['convocationId']);
            if (!$this->isSameStructure($loggedInUser, $convocation) || !$this->isAuthorizedUser($loggedInUser, $convocation)) {
                return false;
            }
        }

        return true;
    }

    private function isSameStructure(User $loggedInUser, ?Convocation $convocation): bool
    {
        if (!$convocation) {
            return false;
        }

        return $loggedInUser->getStructure()->getId() === $convocation->getSitting()->getStructure()->getId();
    }

    private function isAuthorizedUser(User $user, ?Convocation $convocation): bool
    {
        if (!$convocation) {
            return false;
        }
        if ($this->security->isGranted('ROLE_SECRETARY')) {
            return $this->isInAuthorisedType($user->getAuthorizedTypes(), $convocation->getSitting()->getType());
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
