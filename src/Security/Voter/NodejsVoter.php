<?php

namespace App\Security\Voter;

use App\Security\Http403Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NodejsVoter extends Voter
{
    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['FROM_NODE'])
            && ($subject instanceof Request);
    }

    /**
     * @param Request $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $data = $subject->toArray();
        if ($data['passPhrase'] !== $this->bag->get('nodejs_passphrase')) {
            throw new Http403Exception('Not authorized');
        }

        return true;
    }
}
