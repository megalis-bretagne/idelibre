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

        $this->checkRole($data['role'] ?? null);

        return $this->checkParty($data['party'] ?? null, $structure);
    }

    private function checkRole(?string $roleId)
    {
        if(!$roleId) {
            return;
        }

        $role = $this->roleRepository->find($roleId);
        if (!$role->getIsInStructureRole()) {
            throw new Http403Exception("You can't give role : $roleId");
        }
    }

    private function checkParty(?string $partyId, Structure $structure): bool
    {
        if (!$partyId) {
            return true;
        }

        $party = $this->partyRepository->findBy(['id' => $partyId, 'structure' => $structure]);

        if (empty($party)) {
            throw new Http403Exception("You can't use party : $partyId");
        }

        return !empty($party);
    }
}
