<?php

namespace App\EventListener;

use App\Entity\Party;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;

class PartySaveNotifier
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function preFlush(Party $party, PreFlushEventArgs $event): void
    {
        if (null !== $party->getLegacyId()) {
            return;
        }

        if ('test' === getenv('APP_ENV')) {
            $party->setLegacyId(99999);

            return;
        }

        $conn = $this->em->getConnection();
        $sql = "select nextval('party_legacy_seq');";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $nextVal = $stmt->fetchOne();

        $party->setLegacyId($nextVal);
    }
}
