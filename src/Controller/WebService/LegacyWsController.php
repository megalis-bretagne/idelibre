<?php

namespace App\Controller\WebService;

use App\Security\Http403Exception;
use App\Service\LegacyWs\LegacyWsAuthentication;
use App\Service\LegacyWs\LegacyWsService;
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
    public function addSitting(Request $request, LegacyWsService $wsService, LegacyWsAuthentication $legacyWsAuthentication): JsonResponse
    {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $conn = $request->request->get('conn');
        $jsonData = $request->request->get('jsonData');

        if (!$username || !$plainPassword || !$conn || !$jsonData) {
            throw new BadRequestHttpException('fields jsonData, username, password and conn must be set');
        }

        $structure = $legacyWsAuthentication->getStructureFromLegacyConnection($request->request->get('conn'));

        if (!$structure) {
            throw new Http403Exception('Erreur de structure');
        }

        $userLoggedIn = $legacyWsAuthentication->loginUser($structure, $username . '@' . $structure->getSuffix(), $plainPassword);

        if (!$userLoggedIn) {
            throw new Http403Exception("Erreur d'authentification");
        }

        $rawSitting = json_decode($jsonData, true);
        if (!$rawSitting) {
            throw new BadRequestHttpException('jsonData is not a valid json');
        }

        try {
            $sitting = $wsService->createSitting($rawSitting, $request->files->all(), $structure);
        } catch (\Exception $e) {
            dd($e);
        }

        //TODO check ilf already exist sitting same date  same structure!

        return $this->json([
            'success' => true,
            'code' => 'Seance.add.ok',
            'message' => 'La séance a bien été ajoutée.',
            'uuid' => $sitting->getId(),
        ]);
    }
}
