<?php

namespace App\Controller\api;

use App\Entity\Sitting;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SittingController extends AbstractController
{
    /**
     * @Route("/api/sittings/{id}/sendConvocations", name="api_convocations_send", methods={"POST"})
     */
    public function sendConvocations(Sitting $sitting, ConvocationManager $convocationManager, Request $request): JsonResponse
    {
        //TODO query parameter send All, ators, guests, employees,
        $convocationManager->sendAllConvocations($sitting, $request->get('userProfile'));

        return $this->json(['success' => true]);
    }
}
