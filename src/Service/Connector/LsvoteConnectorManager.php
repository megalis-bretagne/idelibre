<?php

namespace App\Service\Connector;

use App\Entity\Connector\Exception\LsvoteConnectorException;
use App\Entity\Connector\LsvoteConnector;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\LsvoteConnectorRepository;
use App\Repository\UserRepository;
use App\Service\Connector\Lsvote\LsvoteClient;
use App\Service\Connector\Lsvote\LsvoteException;
use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use App\Service\Connector\Lsvote\Model\LsvoteProject;
use App\Service\Connector\Lsvote\Model\LsvoteSitting;
use App\Service\Connector\Lsvote\Model\LsvoteVoter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class LsvoteConnectorManager
{

    public function __construct(
        private readonly LsvoteConnectorRepository $lsvoteConnectorRepository,
        private readonly LsvoteClient              $lsvoteClient,
        private readonly EntityManagerInterface    $entityManager,
        private readonly UserRepository            $userRepository,
        private readonly LoggerInterface           $logger,

    )
    {
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
        $this->em->persist($connector);
        $this->em->flush();
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

    public function createSitting(?string $url, ?string $apiKey, Sitting $sitting): ?string
    {
        $connector = $this->lsvoteConnectorRepository->findOneBy(["structure" => $sitting->getStructure()]);

        $lsvoteSitting = new LsvoteEnveloppe();

        $lsvoteSitting
            ->setSitting($this->prepareLsvoteSitting($sitting))
            ->setProjects($this->prepareLsvoteProjects($sitting))
            ->setVoters($this->prepareLsvoteVoter($sitting));


        try {
            $id = $this->lsvoteClient->sendSitting($connector->getUrl(), $connector->getApiKey(), $lsvoteSitting);
            //   $sitting->getVote()->setLsvoteId($id)  Sauvegarder les infos de lsvote dans IL
            return $id;
        } catch (LsvoteException $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * @return array<LsvoteProject>
     */
    public function prepareLsvoteProjects(Sitting $sitting): array
    {
        $lsvoteProjects = [];
        foreach ($sitting->getProjects() as $project) {
            $lsvoteProject = new LsvoteProject();
            $lsvoteProject->setName($project->getName())
                ->setRank($project->getRank());
            $lsvoteProjects[] = $lsvoteProject;
        }

        return $lsvoteProjects;
    }


    /**
     * @return array<LsvoteVoter>
     */
    public function prepareLsvoteVoter(Sitting $sitting): array
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

    private function prepareLsvoteSitting(Sitting $sitting): LsvoteSitting
    {
        $lsvoteSitting = new LsvoteSitting();

        $lsvoteSitting->setName($sitting->getName())
            ->setDate($sitting->getDate()->format('y-m-d H:i'));

        return $lsvoteSitting;
    }

    public function deleteSitting(Sitting $sitting): bool
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

}