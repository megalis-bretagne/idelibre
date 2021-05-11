<?php

namespace App\Controller\WebService;


use App\Security\Http403Exception;
use App\Service\LegacyWs\LegacyWsService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        if(!$username || !$plainPassword || !$conn || !$jsonData) {
            throw new BadRequestHttpException("fields jsonData, username, password and conn must be set");
        }

        $structure = $wsService->getStructureFromLegacyConnection($request->request->get('conn'));

        if(!$structure) {
            throw new Http403Exception("Erreur de structure");
        }

        $userLoggedIn = $wsService->loginUser($structure, $username . '@' . $structure->getSuffix(), $plainPassword);

        if(!$userLoggedIn) {
            throw new Http403Exception("Erreur d'authentification");
        }

        $sittingData = json_decode($jsonData, true);
        if(!$sittingData) {
            throw new BadRequestHttpException("jsonData is not a valid json");
        }

        $rawActors = json_decode($sittingData['acteurs_convoques'], true);
        if(!$rawActors) {
            throw new BadRequestHttpException('acteurs_convoques is not a valid json');
        }


        dd($request->files->all());


        dd('ok');
    }
}
