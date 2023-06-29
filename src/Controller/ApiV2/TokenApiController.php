<?php

namespace App\Controller\ApiV2;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Service\Zip\ZipTokenGenerator;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v2/structures/{structureId}/sittings/{sittingId}/token')]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class TokenApiController extends AbstractController
{
    #[Route('', name: 'get_timeStamp_zip', methods: ['GET'])]
    public function getAll(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['sittingId' => 'id'])] Sitting $sitting,
        ZipTokenGenerator $zipTokenGenerator
    ): Response {
        $response = new BinaryFileResponse($zipTokenGenerator->generateZipToken($sitting));

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '_' . $sitting->getDate()->format('d_m_Y_H_i') . '_jetons.zip'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
