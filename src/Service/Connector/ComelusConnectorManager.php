<?php

namespace App\Service\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\ComelusApiWrapper\ComelusException;
use Libriciel\ComelusApiWrapper\ComelusWrapper;
use Nyholm\Psr7\UploadedFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ComelusConnectorManager
{
    private EntityManagerInterface $em;
    private ComelusConnectorRepository $comelusConnectorRepository;
    private ComelusWrapper $comelusWrapper;
    private LoggerInterface $logger;
    private FileManager $fileManager;

    public function __construct(
        EntityManagerInterface $em,
        ComelusConnectorRepository $comelusConnectorRepository,
        ComelusWrapper $comelusWrapper,
        LoggerInterface $logger,
        FileManager $fileManager
    ) {
        $this->em = $em;
        $this->comelusConnectorRepository = $comelusConnectorRepository;
        $this->comelusWrapper = $comelusWrapper;
        $this->logger = $logger;
        $this->fileManager = $fileManager;
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

    public function sendComelus(Sitting $sitting): ?string
    {
        $comelusConnetor = $this->comelusConnectorRepository->findOneBy(['structure' => $sitting->getStructure()]);

        if (!$comelusConnetor->getActive() || !$comelusConnetor->getMailingListId()) {
            throw new BadRequestHttpException('Comelus is not enabled');
        }
        $uploadedFiles = $this->prepareFiles($sitting);

        $this->comelusWrapper->setApiKey($comelusConnetor->getApiKey());
        $this->comelusWrapper->setUrl($comelusConnetor->getUrl());

        $response = $this->comelusWrapper->createDocument($sitting->getName(), $comelusConnetor->getMailingListId(), $comelusConnetor->getDescription(), $uploadedFiles);

        $comelusId = $response['id'] ?? null;

        if ($comelusId) {
            $this->comelusWrapper->sendDocument($comelusId);
        }

        $sitting->setComelusId($comelusId);
        $this->em->flush();

        return $comelusId;
    }

    /**
     * @return UploadedFile[]
     */
    private function prepareFiles(Sitting $sitting): array
    {
        $files = $this->fileManager->listFilesFromSitting($sitting);
        $uploadedFiles = [];

        foreach ($files as $file) {
            $uploadedFiles[] = new UploadedFile($file->getPath(), 0, 0, $file->getName());
        }

        return $uploadedFiles;
    }
}
