<?php


namespace App\Controller\api;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Service\Convocation\ConvocationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConvocationController extends AbstractController
{
    /**
     * @Route("/api/convocations/{id}", name="api_convocation_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getConvocations(Sitting $sitting, ConvocationRepository $convocationRepository): Response
    {
        return $this->json(
            $convocationRepository->getConvocationsBySitting($sitting),
            200,
            [],
            ['groups' => ['convocation', 'user']]
        );
    }


    /**
     * @Route("/api/convocations/{id}/send", name="api_convocation_send", methods={"POST"})
     */
    public function sendConvocation(Convocation $convocation, ConvocationManager $convocationManager): Response
    {
        $convocationManager->sendConvocations([$convocation]);

        return $this->json($convocation, 200, [], ['groups' => ['convocation', 'user']]);
    }
}
