<?php

namespace App\Service\EventLog;


use App\Entity\EventLog\Action;
use App\Entity\EventLog\EventLog;
use Symfony\Bundle\SecurityBundle\Security;


class EventLogManager
{
    public function __construct(private readonly Security $security)
    {
    }

    public function logEvent(Action $action, string $targetId, string $targetName): void
    {

        dd($this->security->getUser());

        $eventLog = (new EventLog())
            ->setAction($action)
            ->setTargetId($targetId)
            ->setTargetName($targetName)
            ->setAuthorId($this->security->getUser()->getId() ?? "SCRIPT")
            ->setAuthorName($this->security->getUser()->getName() ?? "SCRIPT");
        ;
    }


}