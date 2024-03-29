<?php

namespace App\Service\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use App\Service\Util\DateUtil;
use App\Service\Util\Sanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\ComelusApiWrapper\ComelusException;
use Libriciel\ComelusApiWrapper\ComelusWrapper;
use Libriciel\ComelusApiWrapper\Model\ComelusDocument;
use Nyholm\Psr7\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ComelusConnectorManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ComelusConnectorRepository $comelusConnectorRepository,
        private readonly ComelusWrapper $comelusWrapper,
        private readonly DateUtil $dateUtil,
        private readonly ComelusContentGenerator $comelusContentGenerator,
        private readonly ProjectRepository $projectRepository,
        private readonly OtherdocRepository $otherdocRepository,
        private readonly Sanitizer $sanitizer,
        private readonly FileGenerator $fileGenerator,
        private readonly Filesystem $filesystem
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

    public function isAlreadyCreated(Structure $structure): bool
    {
        return null !== $this->comelusConnectorRepository->findOneBy(['structure' => $structure]);
    }

    public function save(ComelusConnector $comelusConnector): void
    {
        $this->em->persist($comelusConnector);
        $this->em->flush();
    }

    public function checkApiKey(string $url, string $apiKey): bool
    {
        //        dd($apiKey);
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

    /**
     * @throws UnsupportedExtensionException
     * @throws ComelusException
     */
    public function sendComelus(Sitting $sitting): ?string
    {
        $comelusConnetor = $this->comelusConnectorRepository->findOneBy(['structure' => $sitting->getStructure()]);

        if (!$comelusConnetor->getActive() || !$comelusConnetor->getMailingListId()) {
            throw new BadRequestHttpException('Comelus is not enabled');
        }

        $uploadedFiles = $this->prepareFiles($sitting);

        $this->comelusWrapper->setApiKey($comelusConnetor->getApiKey());
        $this->comelusWrapper->setUrl($comelusConnetor->getUrl());


        $comelusDocument = new ComelusDocument();
        $comelusDocument->setName($this->getDocumentName($sitting));
        $comelusDocument->setMailingListId($comelusConnetor->getMailingListId());
        $comelusDocument->setDescription($this->comelusContentGenerator->createDescription($comelusConnetor->getDescription(), $sitting));
        $comelusDocument->setFiles($uploadedFiles);

        $response = $this->comelusWrapper->createDocument($comelusDocument);

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
     * @throws UnsupportedExtensionException
     */
    private function prepareFiles(Sitting $sitting): array
    {
        $projects = $this->projectRepository->getProjectsBySitting($sitting);
        $otherDocs = $this->otherdocRepository->getOtherdocsBySitting($sitting);
        $uploadedFiles = [];

        foreach ($otherDocs as $otherDoc) {
            $uploadedFiles[] = $this->uploadOtherDocHelper($otherDoc);
        }

        foreach ($projects as $project) {
            $uploadedFiles[] = $this->uploadProjectHelper($project);

            foreach ($project->getAnnexes() as $annex) {
                $uploadedFiles[] = $this->uploadAnnexesHelper($annex);
            }
        }

        if ($this->getZipHelper($sitting)) {
            $uploadedFiles[] = $this->getZipHelper($sitting);
        }

        return $uploadedFiles;
    }

    private function uploadProjectHelper($project): UploadedFile
    {
        return new UploadedFile($project->getFile()->getPath(), 0, 0, $project->getRank() + 1 . '. ' . $this->sanitizer->fileNameSanitizer($project->getName(), 150) . '.pdf');
    }

    private function uploadOtherDocHelper($otherDoc): UploadedFile
    {
        return new UploadedFile($otherDoc->getFile()->getPath(), 0, 0, $this->sanitizer->fileNameSanitizer($otherDoc->getName(), 150) . '.pdf');
    }

    private function uploadAnnexesHelper($annex): UploadedFile
    {
        return new UploadedFile($annex->getFile()->getPath(), 0, 0, '- ' . $annex->getFile()->getName());
    }

    /**
     * @throws UnsupportedExtensionException
     */
    private function getZipHelper(Sitting $sitting): ?UploadedFile
    {
        $zipPath = $this->fileGenerator->genFullSittingDirPath($sitting, 'zip');

        if (!$this-> filesystem->exists($zipPath)) {
            return null;
        }

        return new UploadedFile($zipPath, 0, 0, 'seance-complete.zip');
    }
}
