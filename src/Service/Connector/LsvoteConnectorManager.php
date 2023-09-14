<?php

namespace App\Service\Connector;

use App\Entity\Connector\Exception\LsvoteConnectorException;
use App\Entity\Connector\LsvoteConnector;
use App\Entity\LsvoteSitting;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\LsvoteConnectorRepository;
use App\Repository\UserRepository;
use App\Service\Connector\Lsvote\LsvoteClient;
use App\Service\Connector\Lsvote\LsvoteException;
use App\Service\Connector\Lsvote\LsvoteNotFoundException;
use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use App\Service\Connector\Lsvote\Model\LsvoteProject;
use App\Service\Connector\Lsvote\Model\LsvoteVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class LsvoteConnectorManager
{
    public function __construct(
        private readonly LsvoteConnectorRepository $lsvoteConnectorRepository,
        private readonly LsvoteClient              $lsvoteClient,
        private readonly EntityManagerInterface    $entityManager,
        private readonly UserRepository            $userRepository,
        private readonly LoggerInterface           $logger,
    ) {
    }

    /**
     * @throws LsvoteConnectorException
     */
    public function createConnector(Structure $structure): void
    {
        if ($this->isAlreadyCreated($structure)) {
            throw new LsvoteConnectorException('Already created lsvoteConnectorManager');
        }
        $connector = new LsvoteConnector($structure);
        $this->entityManager->persist($connector);
        $this->entityManager->flush();
    }

    private function isAlreadyCreated(Structure $structure): bool
    {
        return null !== $this->lsvoteConnectorRepository->findOneBy(['structure' => $structure]);
    }

    public function save(LsvoteConnector $lsvoteConnector): void
    {
        $this->entityManager->persist($lsvoteConnector);
        $this->entityManager->flush();
    }

    public function getLsvoteConnector(Structure $structure): LsvoteConnector
    {
        return $this->lsvoteConnectorRepository->findOneBy(['structure' => $structure]);
    }

    public function checkApiKey(?string $url, ?string $apiKey): bool
    {
        try {
            $this->lsvoteClient->checkApiKey($url, $apiKey);
        } catch (LsvoteException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @throws LsvoteSittingCreationException
     */
    public function createSitting(Sitting $sitting): ?string
    {
        $connector = $this->getLsvoteConnector($sitting->getStructure());

        $lsvoteSitting = new LsvoteEnveloppe();
        $lsvoteSitting
            ->setSitting($this->prepareLsvoteSitting($sitting))
            ->setProjects($this->prepareLsvoteProjects($sitting))
            ->setVoters($this->prepareLsvoteVoter($sitting));

        try {
            $id = $this->lsvoteClient->sendSitting($connector->getUrl(), $connector->getApiKey(), $lsvoteSitting);

            $this->createLsvotesitting($id, $sitting);

            return $id;
        } catch (LsvoteException $e) {
            $this->logger->error($e->getMessage());

            throw new LsvoteSittingCreationException("Erreur lors de la création de la séance");
        }
    }


    /**
     * @return array<LsvoteProject>
     */
    private function prepareLsvoteProjects(Sitting $sitting): array
    {
        $lsvoteProjects = [];
        foreach ($sitting->getProjects() as $project) {
            $lsvoteProject = new LsvoteProject();
            $lsvoteProject
                ->setExternalId($project->getId())
                ->setRank($project->getRank())
                ->setName($project->getName());
            $lsvoteProjects[] = $lsvoteProject;
        }

        return $lsvoteProjects;
    }


    /**
     * @return array<LsvoteVoter>
     */
    private function prepareLsvoteVoter(Sitting $sitting): array
    {
        /** @var array<User> $users */
        $users = $this->userRepository->findActorsInSitting($sitting)->getQuery()->getResult();

        $lsvoteVoters = [];
        foreach ($users as $user) {
            $lsvoteVoter = new LsvoteVoter();
            $lsvoteVoter->setIdentifier($user->getId())
                ->setLastName($user->getLastName())
                ->setFirstName($user->getFirstName());
            $lsvoteVoters[] = $lsvoteVoter;
        }

        return $lsvoteVoters;
    }

    private function prepareLsvoteSitting(Sitting $sitting): \App\Service\Connector\Lsvote\Model\LsvoteSitting
    {
        $lsvoteSitting = new \App\Service\Connector\Lsvote\Model\LsvoteSitting();

        $lsvoteSitting->setName($sitting->getName())
            ->setDate($sitting->getDate()->format('y-m-d H:i'));


        return $lsvoteSitting;
    }


    /**
     * @param mixed $id
     * @param Sitting $sitting
     * @return void
     */
    private function createLsvotesitting(mixed $id, Sitting $sitting): void
    {
        $lsvoteSitting = (new LsvoteSitting())
            ->setLsvoteSittingId($id)
            ->setSitting($sitting);

        $this->entityManager->persist($lsvoteSitting);
        $this->entityManager->flush();
    }


    public function deleteLsvoteSitting(Sitting $sitting): bool
    {
        $lsvoteSittingId = $sitting->getLsvoteSitting()->getLsvoteSittingId();
        $connector = $this->getLsvoteConnector($sitting->getStructure());

        try {
            $this->lsvoteClient->deleteSitting($connector->getUrl(), $connector->getApiKey(), $lsvoteSittingId);
        } catch (LsvoteException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @throws LsvoteResultException
     */
    public function getLsvoteSittingResults(Sitting $sitting): array
    {
        $connector = $this->getLsvoteConnector($sitting->getStructure());

        try {
            $results = $this->lsvoteClient->resultSitting($connector->getUrl(), $connector->getApiKey(), $sitting->getLsvoteSitting()->getLsvoteSittingId());

            $this->saveResults($sitting, $results);
            return $results;
        } catch (LsvoteException $e) {
            $this->logger->error($e->getMessage());
            throw new LsvoteResultException($this->formatError($e->getMessage()));
        }
    }


    private function formatError(string $message): string
    {
        return match ($message) {
            '"sitting.not.over"' => "La séance n'est pas terminée sur lsvote",
            default => 'Une erreur est survenue. Veuillez vérifier que la séance sur lsvote soit terminée et n\'ait pas été effacée.',
        };
    }

    /**
     * @param Sitting $sitting
     * @param array $results
     * @return void
     */
    public function saveResults(Sitting $sitting, array $results): void
    {
        $lsvoteSitting = $sitting->getLsvoteSitting();
        $lsvoteSitting->setResults($results);
        $this->entityManager->flush();
    }

    public function createJsonFile(Sitting $sitting): string
    {
        $results = $sitting->getLsvoteSitting()->getResults();
        # pas de d'ID externe ici...
        $resultsJson = json_encode($results);

        $jsonPath = '/tmp/' . uniqid('json_vote');

        file_put_contents($jsonPath, $resultsJson);

        return $jsonPath;
    }

    /**
     * @throws LsvoteSittingCreationException
     * @throws LsvoteException
     */
    public function editLsvoteSitting(Sitting $sitting): ?string
    {
        [$connector, $lsvoteEnvelope, $lsvotesittingId] = $this->prepareEdit($sitting);

        try {
            $id = $this->lsvoteClient->reSendSitting($connector->getUrl(), $connector->getApiKey(), $lsvotesittingId, $lsvoteEnvelope);
            $sitting->getLsvoteSitting()->setLsvoteSittingId($id);
            $this->entityManager->flush();
            return $id;
        } catch (LsvoteNotFoundException $e) {
            $id = $this->editIfSittingWasDeleted($sitting, $connector, $lsvoteEnvelope);
            $sitting->getLsvoteSitting()->setLsvoteSittingId($id);
            $this->entityManager->flush();
            return $id;
        } catch (LsvoteException $e) {
            throw new LsvoteException($e->getMessage());
        }
    }

    /**
     * @param Sitting $sitting
     * @return array
     */
    public function prepareEdit(Sitting $sitting): array
    {
        $connector = $this->getLsvoteConnector($sitting->getStructure());
        $lsvoteEnvelope = new LsvoteEnveloppe();

        $lsvoteEnvelope
            ->setSitting($this->prepareLsvoteSitting($sitting))
            ->setProjects($this->prepareLsvoteProjects($sitting))
            ->setVoters($this->prepareLsvoteVoter($sitting));

        $lsvotesittingId = $sitting->getLsvoteSitting()->getLsvoteSittingId();
        return [$connector, $lsvoteEnvelope, $lsvotesittingId];
    }

    /**
     * @param LsvoteException|Exception $e
     * @param Sitting $sitting
     * @param mixed $connector
     * @param mixed $lsvoteEnvelope
     * @return mixed
     * @throws LsvoteSittingCreationException
     */
    public function editIfSittingWasDeleted(Sitting $sitting, mixed $connector, mixed $lsvoteEnvelope): mixed
    {
        try {
            return $this->lsvoteClient->sendSitting($connector->getUrl(), $connector->getApiKey(), $lsvoteEnvelope);
        } catch (LsvoteException $e) {
            throw new LsvoteSittingCreationException("Impossible de mettre à jour la séance");
        }
    }
}
