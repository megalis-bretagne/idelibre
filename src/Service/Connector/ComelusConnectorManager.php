<?php

namespace App\Service\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\ProjectRepository;
use App\Service\Util\DateUtil;
use App\Service\Util\Sanitizer;
use App\Service\Zip\ZipSittingGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\ComelusApiWrapper\ComelusException;
use Libriciel\ComelusApiWrapper\ComelusWrapper;
use Nyholm\Psr7\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ComelusConnectorManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ComelusConnectorRepository $comelusConnectorRepository,
        private ComelusWrapper $comelusWrapper,
        private DateUtil $dateUtil,
        private ComelusContentGenerator $comelusContentGenerator,
        private ZipSittingGenerator $zipSittingGenerator,
        private ProjectRepository $projectRepository,
        private Sanitizer $sanitizer,
    ) {
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

        $response = $this->comelusWrapper->createDocument(
            $this->getDocumentName($sitting),
            $comelusConnetor->getMailingListId(),
            $this->comelusContentGenerator->createDescription($comelusConnetor->getDescription(), $sitting),
            $uploadedFiles
        );

        $comelusId = $response['id'] ?? null;

        if ($comelusId) {
            $this->comelusWrapper->sendDocument($comelusId);
        }

        $sitting->setComelusId($comelusId);
        $this->em->flush();

        return $comelusId;
    }

    private function getDocumentName(Sitting $sitting): string
    {
        $formattedDateTime = $this->dateUtil->getFormattedDateTime($sitting->getDate(), $sitting->getStructure()->getTimezone()->getName());

        return $sitting->getName() . ' ' . $formattedDateTime;
    }

    /**
     * @return UploadedFile[]
     */
    private function prepareFiles(Sitting $sitting): array
    {
        $projects = $this->projectRepository->getProjectsBySitting($sitting);
        $uploadedFiles = [];

        foreach ($projects as $project) {
            $uploadedFiles[] = $this->uploadProjectHelper($project);

            foreach ($project->getAnnexes() as $annex) {
                $uploadedFiles[] = $this->uploadAnnexesHelper($annex);
            }
        }

        $uploadedFiles[] = $this->uploadZipHelper($sitting);

        return $uploadedFiles;
    }

    private function uploadProjectHelper($project): UploadedFile
    {
        return new UploadedFile($project->getFile()->getPath, 0, 0, $project->getRank() + 1 . '. ' . $this->sanitizer->fileNameSanitizer($project->getName(), 150) . '.pdf');
    }

    private function uploadAnnexesHelper($annex): UploadedFile
    {
        return new UploadedFile($annex->getFile()->getPath(), 0, 0, '- ' . $annex->getFile()->getName());
    }

    private function uploadZipHelper(Sitting $sitting): UploadedFile
    {
        return new UploadedFile($this->zipSittingGenerator->generateZipSitting($sitting), 0, 0, 'seance-complete.zip');
    }
}
