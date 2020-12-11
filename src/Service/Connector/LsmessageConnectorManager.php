<?php

namespace App\Service\Connector;

use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\Structure;
use App\Repository\Connector\LsmessageConnectorRepository;
use Doctrine\ORM\EntityManagerInterface;

class LsmessageConnectorManager
{
    private EntityManagerInterface $em;
    private LsmessageConnectorRepository $lsmessageConnectorRepository;

    public function __construct(EntityManagerInterface $em, LsmessageConnectorRepository $lsmessageConnectorRepository)
    {
        $this->em = $em;
        $this->lsmessageConnectorRepository = $lsmessageConnectorRepository;
    }

    /**
     * @throws LsmessageConnectorException
     */
    public function createConnector(Structure $structure): void
    {
        if ($this->isAlreadyCreated($structure)) {
            throw new LsmessageConnectorException('Already created lsmessageConnector');
        }
        $connector = new LsmessageConnector($structure);
        $this->em->persist($connector);
        $this->em->flush();
    }

    private function isAlreadyCreated(Structure $structure): bool
    {
        return null !== $this->lsmessageConnectorRepository->findOneBy(['structure' => $structure]);
    }

    public function save(LsmessageConnector $lsmessageConnector): void
    {
        $this->em->persist($lsmessageConnector);
        $this->em->flush();
    }
}
