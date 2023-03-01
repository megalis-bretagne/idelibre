<?php

namespace App\MessageHandler\EventLog;

use App\Entity\EventLog\Action;
use App\Entity\EventLog\EventLog;
use App\Entity\User;
use App\Service\EventLog\EventLogManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postAction', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postAction', entity: User::class)]
class UserEventHandler
{
    private ?EventLog $eventLog = null;

    public function __construct(private readonly EventLogManager $eventLogManager)
    {
    }

    public function postPersist(User $user, LifecycleEventArgs $args): void
    {
        $this->eventLogManager->createLog(Action::USER_CREATE, $user->getId(), $user->getUsername(), $user->getStructure()?->getId());
    }

    public function preRemove(User $user, LifecycleEventArgs $args): void
    {
        $this->eventLog = $this->eventLogManager->createLog(
            Action::USER_DELETE,
            $user->getId(),
            $user->getUsername(),
            $user->getStructure()?->getId(),
            false
        );
    }

    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('password')) {
            $this->eventLog = $this->eventLogManager->createLog(
                Action::USER_PASSWORD_UPDATED,
                $user->getId(),
                $user->getUsername(),
                $user->getStructure()?->getId(),
                false
            );
        }
    }

    public function postAction(): void
    {
        if ($this->eventLog) {
            $this->eventLogManager->saveLog($this->eventLog);
        }
        $this->eventLog = null;
    }
}
