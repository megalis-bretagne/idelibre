<?php

namespace App\Service\Connector;

use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\Connector\LsmessageConnectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\LsMessageWrapper;
use Libriciel\LsMessageWrapper\Sms;
use Psr\Log\LoggerInterface;

class LsmessageConnectorManager
{
    private EntityManagerInterface $em;
    private LsmessageConnectorRepository $lsmessageConnectorRepository;

    private LsMessageWrapper $lsMessageWrapper;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        LsmessageConnectorRepository $lsmessageConnectorRepository,
        LsMessageWrapper $lsMessageWrapper,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->lsmessageConnectorRepository = $lsmessageConnectorRepository;
        $this->lsMessageWrapper = $lsMessageWrapper;
        $this->logger = $logger;
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

    public function checkApiKey(?string $url, ?string $apiKey): ?array
    {
        try {
            $this->lsMessageWrapper->setUrl($url);
            $this->lsMessageWrapper->setApiKey($apiKey);

            return $this->lsMessageWrapper->info();
        } catch (LsMessageException $e) {
            return null;
        }
    }

    /**
     * @param Sms[] $sms
     */
    public function sendSms(Sitting $sitting, array $smsList)
    {
        $lsmessageConnector = $this->getLsmessageConnector($sitting->getStructure());
        if (!$lsmessageConnector || !$lsmessageConnector->getActive()) {
            return;
        }
        $this->lsMessageWrapper->setUrl($lsmessageConnector->getUrl());
        $this->lsMessageWrapper->setApiKey($lsmessageConnector->getApiKey());
        try {
            $this->lsMessageWrapper->sendMultiple($smsList);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function getLsmessageConnector(Structure $structure): LsmessageConnector
    {
        return $this->lsmessageConnectorRepository->findOneBy(['structure' => $structure]);
    }
}
