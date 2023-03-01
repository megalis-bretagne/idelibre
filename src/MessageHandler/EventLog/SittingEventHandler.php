<?php

namespace App\MessageHandler\EventLog;

use App\Entity\EventLog\Action;
use App\Entity\EventLog\EventLog;
use App\Entity\Sitting;
use App\Service\EventLog\EventLogManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Sitting::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postAction', entity: Sitting::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Sitting::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postAction', entity: Sitting::class)]
class SittingEventHandler
{
    private ?EventLog $eventLog = null;

    public function __construct(private readonly EventLogManager $eventLogManager)
    {
    }


    public function preRemove(Sitting $sitting, LifecycleEventArgs $args): void
    {
        $this->eventLog = $this->eventLogManager->createLog(
            Action::SITTING_DELETE,
            $sitting->getId(),
            $sitting->getNameWithDate(),
            $sitting->getStructure()?->getId(),
            false
        );
    }

    public function preUpdate(Sitting $sitting, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('isArchived') && $sitting->getIsArchived()) {
            $this->eventLog = $this->eventLogManager->createLog(
                Action::SITTING_ARCHIVED,
                $sitting->getId(),
                $sitting->getNameWithDate(),
                $sitting->getStructure()?->getId(),
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
