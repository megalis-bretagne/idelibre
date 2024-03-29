<?php

namespace App\Controller\api;

use App\Entity\Sitting;
use App\Message\UpdatedSitting;
use App\Service\ApiEntity\OtherdocApi;
use App\Service\Otherdoc\OtherdocManager;
use App\Service\Pdf\PdfValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class OtherdocController extends AbstractController
{
    #[Route(path: '/api/otherdocs/{id}', name: 'api_otherdoc_add', methods: ['POST'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function edit(Sitting $sitting, Request $request, SerializerInterface $serializer, OtherdocManager $otherdocManager, MessageBusInterface $messageBus, PdfValidator $pdfValidator): JsonResponse
    {
        $rawOtherdocs = $request->request->get('otherdocs');

        $otherdocs = $serializer->deserialize($rawOtherdocs, OtherdocApi::class . '[]', 'json');

        if (!$pdfValidator->isOtherdocsPdf($otherdocs)) {
            return $this->json(['success' => false, 'message' => 'Au moins un document n\'est pas un pdf'], 400);
        }

        $unreadablePdf = $pdfValidator->getListOfUnreadablePdf($request->files->all());
        if (count($unreadablePdf) > 0) {
            return $this->json(['success' => false, 'message' => 'Fichier(s) invalide(s) :  ' . implode(', ', $unreadablePdf)], 400);
        }

        $otherdocManager->update($otherdocs, $request->files->all(), $sitting);

        $messageBus->dispatch(new UpdatedSitting($sitting->getId()));


        return $this->json(['success' => true]);
    }

    #[Route(path: '/api/otherdocs/{id}', name: 'api_otherdoc_get', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function getOtherdocsFromSitting(Sitting $sitting, OtherdocManager $otherdocManager): JsonResponse
    {
        $otherdocsApi = $otherdocManager->getApiOtherdocsFromOtherdocs($otherdocManager->getOtherdocsFromSitting($sitting));

        return $this->json($otherdocsApi);
    }
}
