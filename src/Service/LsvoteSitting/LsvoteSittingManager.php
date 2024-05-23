<?php

namespace App\Service\LsvoteSitting;

use App\Entity\Sitting;
use App\Repository\LsvoteSittingRepository;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\Connector\LsvoteResultException;
use Doctrine\ORM\EntityManagerInterface;

class LsvoteSittingManager
{

    public function __construct(
        private readonly LsvoteSittingRepository $lsvoteSittingRepository,
        private readonly LsvoteConnectorManager $lsvoteConnectorManager,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getLsvoteSittingResults(Sitting $sitting): array
    {
        $lsvoteSitting = $this->lsvoteSittingRepository->findOneBy(['sitting' => $sitting]);
        if(!$lsvoteSitting) {
            throw new NotExistLsvoteSittingException("Cette séance n'a pas de vote électronique associé");
        }

        if(empty($lsvoteSitting->getResults()) ) {
            try {
                $this->lsvoteConnectorManager->getLsvoteSittingResults($sitting);
            } catch (LsvoteResultException $e) {
                throw new NoLsVoteResultException("Erreur dans la recuperation des resultats : " . $e->getMessage());
            }
            $this->entityManager->refresh($lsvoteSitting);

            if(empty($lsvoteSitting->getResults()) ) {
                throw new NoLsVoteResultException("Aucun résultat n'a été trouvé pour cette séance");
            }
        }

        return $lsvoteSitting->getResults();
    }
    
    
}