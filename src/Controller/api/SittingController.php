<?php

namespace App\Controller\api;

use App\Entity\Sitting;
use App\Service\Convocation\ConvocationManager;
use App\Service\Email\NotificationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SittingController extends AbstractController
{
    /**
     * @Route("/api/sittings/{id}/sendConvocations", name="api_convocations_send", methods={"POST"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function sendConvocations(Sitting $sitting, ConvocationManager $convocationManager, Request $request): JsonResponse
    {
        $convocationManager->sendAllConvocations($sitting, $request->get('userProfile'));

        return $this->json(['success' => true]);
    }

    /**
     * @Route("/api/sittings/{id}/notifyAgain", name="api_sitting_notify_again", methods={"POST"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function notifyAgain(Sitting $sitting, NotificationService $notificationService, Request $request): JsonResponse
    {
        $msg = $request->toArray();
        $notificationService->reNotify($sitting, $msg['object'], $msg['content']);

        return $this->json(['success' => true]);
    }
}
