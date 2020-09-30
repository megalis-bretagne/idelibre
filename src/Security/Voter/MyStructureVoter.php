<?php

namespace App\Security\Voter;

use App\Entity\Document;
use App\Entity\EmailTemplate;
use App\Entity\EventLog;
use App\Entity\File;
use App\Entity\MailingList;
use App\Entity\Recipient;
use App\Entity\Status;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MyStructureVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['MY_STRUCTURE'])
            && (
                $subject instanceof Document || $subject instanceof MailingList || $subject instanceof Status
            || $subject instanceof Recipient || $subject instanceof User || $subject instanceof EventLog
                || $subject instanceof File || $subject instanceof EmailTemplate
            );
    }


    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user->getStructure()->getId() === $subject->getStructure()->getId()) {
            return true;
        }

        return false;
    }
}
