<?php

namespace App\Controller\WebService;

use App\Service\LegacyWs\LegacyWsAuthentication;
use App\Service\LegacyWs\LegacyWsService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class LegacyWsController extends AbstractController
{
    /**
     * @Route("/seance.json", name="wd_connector")
     */
    public function addSitting(
        Request $request,
        LegacyWsService $wsService,
        LegacyWsAuthentication $legacyWsAuthentication,
        LoggerInterface $logger
    ): JsonResponse {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $conn = $request->request->get('conn');
        $jsonData = $request->request->get('jsonData');

        try {
            if (!$username || !$plainPassword || !$conn || !$jsonData) {
                $logger->error('fields jsonData, username, password and conn must be set');
                throw new BadRequestHttpException('fields jsonData, username, password and conn must be set');
            }
            $structure = $legacyWsAuthentication->getStructureFromLegacyConnection($request->request->get('conn'));
            $legacyWsAuthentication->loginUser($structure, $username . '@' . $structure->getSuffix(), $plainPassword);

            $rawSitting = json_decode($jsonData, true);
            if (!$rawSitting) {
                $logger->error('jsonData is not a valid json');
                throw new BadRequestHttpException('jsonData is not a valid json');
            }
            $sitting = $wsService->createSitting($rawSitting, $request->files->all(), $structure);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return $this->json([
                'success' => false,
                'code' => 'Seance.add.error',
                'message' => $e->getMessage(),
            ], 400);
        }

        return $this->json([
            'success' => true,
            'code' => 'Seance.add.ok',
            'message' => 'La séance a bien été ajoutée.',
            'uuid' => $sitting->getId(),
        ]);
    }
}
