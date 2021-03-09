<?php

namespace App\Service\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\ComelusApiWrapper\ComelusException;
use Libriciel\ComelusApiWrapper\ComelusWrapper;

class ComelusConnectorManager
{
    private EntityManagerInterface $em;
    private ComelusConnectorRepository $comelusConnectorRepository;
    private ComelusWrapper $comelusWrapper;

    public function __construct(EntityManagerInterface $em, ComelusConnectorRepository $comelusConnectorRepository, ComelusWrapper $comelusWrapper)
    {
        $this->em = $em;
        $this->comelusConnectorRepository = $comelusConnectorRepository;
        $this->comelusWrapper = $comelusWrapper;
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

    public function checkApiKey(?string $url, ?string $apiKey): bool
    {
        try {
            //$this->comelusWrapper->setApiKey('26e979ac610a01c0ec1933fc4e2c30570d9f47ff6ff85cb55091c1140798eb6878b8ce1bb7ce9f4011d6cf42c66bc4f95bd63faa530347ac43ce7e9f');
            //$this->comelusWrapper->setUrl('https://comelus.libriciel.fr');
            $this->comelusWrapper->setApiKey($apiKey);
            $this->comelusWrapper->setUrl($url);
            $this->comelusWrapper->check();

            return true;
        } catch (ComelusException $e) {
            return false;
        }
    }

    /**
     * @throws ComelusException
     */
    public function getMailingLists(?string $url, ?string $apiKey): array
    {
        $this->comelusWrapper->setApiKey($apiKey);
        $this->comelusWrapper->setUrl($url);

        return $this->comelusWrapper->getMailingLists();
    }
}
