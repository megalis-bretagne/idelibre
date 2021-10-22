<?php

namespace App\Security\Voter\Api;

use App\Entity\Structure;
use App\Repository\ThemeRepository;
use App\Security\Http403Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ThemeRelationVoter extends Voter
{
    public function __construct(private ThemeRepository $themeRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['API_RELATION_THEME']) && ($subject['structure'] instanceof Structure && is_array($subject['data']));
    }

    /**
     * @param Structure $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var Structure $structure */
        $structure = $subject['structure'];
        $data = $subject['data'];

        return $this->VerifyParentTheme($data['parent'] ?? null, $structure);
    }

    private function VerifyParentTheme(?string $themeId, Structure $structure): bool
    {
        if (!$themeId) {
            return true;
        }

        $theme = $this->themeRepository->findBy(['id' => $themeId, 'structure' => $structure]);
        if (empty($theme)) {
            throw new Http403Exception("You cannot use this parent : $themeId");
        }

        return !empty($theme);
    }
}
