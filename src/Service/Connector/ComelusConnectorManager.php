<?php

namespace App\Service\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use Doctrine\ORM\EntityManagerInterface;

class ComelusConnectorManager
{
    private EntityManagerInterface $em;
    private ComelusConnectorRepository $comelusConnectorRepository;

    public function __construct(EntityManagerInterface $em, ComelusConnectorRepository $comelusConnectorRepository)
    {
        $this->em = $em;
        $this->comelusConnectorRepository = $comelusConnectorRepository;
    }

    /**
     * @throws ComelusConnectorException
     */
    public function createConnector(Structure $structure): void
    {
        if ($this->isAlreadyCreated($structure)) {
            throw new ComelusConnectorException('Already created lsmessageConnector');
        }
        $connector = new ComelusConnector($structure);
        $this->em->persist($connector);
        $this->em->flush();
    }

    private function isAlreadyCreated(Structure $structure): bool
    {
        return null !== $this->comelusConnectorRepository->findOneBy(['structure' => $structure]);
    }

    public function save(ComelusConnector $comelusConnector): void
    {
        $this->em->persist($comelusConnector);
        $this->em->flush();
    }
}
