<?php

namespace App\Controller\api;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Service\Convocation\ConvocationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConvocationController extends AbstractController
{
    /**
     * @Route("/api/convocations/{id}", name="api_convocation_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getConvocations(Sitting $sitting, ConvocationRepository $convocationRepository): JsonResponse
    {
        return $this->json(
            [
                'actors' => $convocationRepository->getActorConvocationsBySitting($sitting),
                'guests' => $convocationRepository->getGuestConvocationsBySitting($sitting),
                'employees' => $convocationRepository->getInvitableEmployeeConvocationsBySitting($sitting),
            ],
            200,
            [],
            ['groups' => ['convocation', 'user']]
        );
    }

    /**
     * @Route("/api/convocations/{id}/send", name="api_convocation_send", methods={"POST"})
     * @IsGranted("MANAGE_CONVOCATIONS", subject="convocation")
     */
    public function sendConvocation(Convocation $convocation, ConvocationManager $convocationManager): JsonResponse
    {
        $convocationManager->sendConvocation($convocation);

        return $this->json($convocation, 200, [], ['groups' => ['convocation', 'user']]);
    }

    /**
     * @Route("/api/convocations/attendance", name="api_convocation_attendance", methods={"POST", "PUT"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function setAttendance(ConvocationManager $convocationManager, Request $request): JsonResponse
    {
        $convocationManager->updateConvocationAttendances($request->toArray());

        return $this->json(['success' => 'true']);
    }
}
