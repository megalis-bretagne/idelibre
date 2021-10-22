<?php

namespace App\Security\Voter\Api;

use App\Entity\ApiUser;
use App\Entity\Structure;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class SameStructureVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['API_SAME_STRUCTURE']);
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {


        /** @var Structure $structure */
        $structure = $subject['structure'];

        $paramName = array_keys($subject)[1];
        /** @var StructurableInterface $entity */
        $entity = $subject[$paramName];

        return $entity->getStructure()->getId() === $structure->getId();
    }
}
