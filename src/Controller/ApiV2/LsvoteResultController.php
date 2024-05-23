<?php

namespace App\Controller\ApiV2;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Service\LsvoteSitting\LsvoteSittingManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v2/structures/{structureId}/vote')]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class LsvoteResultController extends AbstractController
{

    #[Route('/sitting/{sittingId}', name: 'get_sitting_vote', methods: ['GET'])]
    public function getSittingResults(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['sittingId' => 'id'])] Sitting     $sitting,
        LsvoteSittingManager $lsvoteSittingManager,
    ): Response
    {

        return $this->json($lsvoteSittingManager->getLsvoteSittingResults($sitting));
    }

}