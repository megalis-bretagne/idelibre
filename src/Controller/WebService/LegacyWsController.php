<?php

namespace App\Controller\WebService;

use App\Security\Http403Exception;
use App\Service\LegacyWs\LegacyWsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class LegacyWsController
{
    /**
     * @Route("/seance.json", name="wd_connector")
     */
    public function addSitting(Request $request, LegacyWsService $wsService)
    {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $conn = $request->request->get('conn');
        $jsonData = $request->request->get('jsonData');

        if (!$username || !$plainPassword || !$conn || !$jsonData) {
            throw new BadRequestHttpException('fields jsonData, username, password and conn must be set');
        }

        $structure = $wsService->getStructureFromLegacyConnection($request->request->get('conn'));

        if (!$structure) {
            throw new Http403Exception('Erreur de structure');
        }

        $userLoggedIn = $wsService->loginUser($structure, $username . '@' . $structure->getSuffix(), $plainPassword);

        if (!$userLoggedIn) {
            throw new Http403Exception("Erreur d'authentification");
        }

        $rawSitting = json_decode($jsonData, true);
        if (!$rawSitting) {
            throw new BadRequestHttpException('jsonData is not a valid json');
        }


        try {
            $wsService->createSitting($rawSitting, $request->files->all(), $structure);
        }catch (\Exception $e) {
            dd($e);
        }
        //dd($request->files->all());

        dd('ok');
    }
}
