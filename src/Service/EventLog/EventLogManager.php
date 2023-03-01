<?php

namespace App\Service\EventLog;

use App\Entity\EventLog\Action;
use App\Entity\EventLog\EventLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EventLogManager
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createLog(Action $action, string $targetId, string $targetName, string $structureId = null, bool $isFlush = true): EventLog
    {
        $eventLog = (new EventLog())
            ->setAction($action)
            ->setTargetId($targetId)
            ->setTargetName($targetName)
            ->setAuthorId($this->security->getUser()?->getId() ?? 'SCRIPT')
            ->setAuthorName($this->security->getUser()?->getUsername() ?? 'SCRIPT')
            ->setStructureId($structureId);

        if ($isFlush) {
            $this->saveLog($eventLog);
        }

        return $eventLog;
    }

    public function saveLog(EventLog $eventLog): void
    {
        $this->entityManager->persist($eventLog);
        $this->entityManager->flush();
    }
}
