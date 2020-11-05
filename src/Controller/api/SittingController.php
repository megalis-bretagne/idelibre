<?php


namespace App\Controller\api;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SittingController extends AbstractController
{

    /**
     * @Route("/api/sittings/{id}/sendConvocations", name="api_convocations_send", methods={"POST"})
     */
    public function sendConvocations(Sitting $sitting, ConvocationRepository $convocationRepository, ConvocationManager $convocationManager)
    {
        $convocationManager->sendConvocations($convocationRepository->findBy(['sitting' => $sitting]));
        return $this->json(['success' => true]);
    }
}
