<?php

namespace App\Security\Voter\Api;

use App\Entity\Role;
use App\Entity\Structure;
use App\Repository\PartyRepository;
use App\Repository\RoleRepository;
use App\Security\Http403Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserRelationVoter extends Voter
{
    public function __construct(private RoleRepository $roleRepository, private PartyRepository $partyRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['API_RELATION_USERS']) && ($subject['structure'] instanceof Structure && is_array($subject['data']));
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var Structure $structure */
        $structure = $subject['structure'];
        $data = $subject['data'];

        $role = $this->checkRole($data['role'] ?? null);
        return $this->checkParty($data['party'] ?? null, $structure, $role);
    }

    private function checkRole(?string $roleId)
    {
        if (!$roleId) {
            throw new \Exception("Role must be set", 400);
        }

        $role = $this->roleRepository->find($roleId);
        if (!$role->getIsInStructureRole()) {
            throw new Http403Exception("You can't give role : $roleId");
        }

        return $role;
    }


    private function checkParty(?string $partyId, Structure $structure, Role $role): bool
    {
        if (!$partyId) {
            return true;
        }

        if($role->getName() !== 'Actor') {
            throw new \Exception("Party must be linked to actor", 400);
        }

        $party = $this->partyRepository->findBy(['id' => $partyId, 'structure' => $structure]);

        if(empty($party)) {
            throw new \Exception("You can't use party : $partyId", 400);
        }

        return !empty($party);
    }
}
