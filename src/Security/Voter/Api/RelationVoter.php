<?php

namespace App\Security\Voter\Api;

use App\Entity\Structure;
use App\Repository\UserRepository;
use App\Security\Http403Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RelationVoter extends Voter
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['API_RELATION_TYPE_USERS']) && ($subject['structure'] instanceof Structure && is_array($subject['data']));
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var Structure $structure */
        $structure = $subject['structure'];
        $data = $subject['data'];

        if (empty($data['associatedUsers'])) {
            return true;
        }

        $inDatabaseUsersWithId = $this->userRepository->findUsersByIds($structure, $data['associatedUsers']);
        $inDataBaseIds = array_map(fn ($el) => $el['id'], $inDatabaseUsersWithId);

        $diff = array_diff($data['associatedUsers'], $inDataBaseIds);

        if (!empty($diff)) {
            throw new Http403Exception('some users does not belong to your structure : ' . implode(', ', $diff));
        }

        return empty($diff);
    }
}
