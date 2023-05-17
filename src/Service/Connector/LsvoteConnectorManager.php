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
use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use App\Service\Connector\Lsvote\Model\LsvoteProject;
use App\Service\Connector\Lsvote\Model\LsvoteSitting;
use App\Service\Connector\Lsvote\Model\LsvoteVoter;
use Doctrine\ORM\EntityManagerInterface;

class LsvoteConnectorManager
{

    public function __construct(
        private readonly LsvoteConnectorRepository $lsvoteConnectorRepository,
        private readonly LsvoteClient              $lsvoteClient,
        private readonly EntityManagerInterface    $entityManager,
        private readonly UserRepository            $userRepository,

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

    public function checkApiKey(?string $url, ?string $apiKey): bool
    {
        return $this->lsvoteClient->checkApiKey($url, $apiKey);
    }

    public function createSitting(?string $url, ?string $apiKey, Sitting $sitting)
    {

        $lsvoteSitting = new LsvoteEnveloppe();

        $lsvoteSitting
            ->setSitting($this->prepareLsvoteSitting($sitting))
            ->setProjects($this->prepareLsvoteProjects($sitting))
            ->setVoters($this->prepareLsvoteVoter($sitting));


        $id = $this->lsvoteClient->sendSitting($url, $apiKey, $lsvoteSitting);
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

}