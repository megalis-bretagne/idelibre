<?php

namespace App\Controller\WebService;

use App\Message\UpdatedSitting;
use App\Security\Http403Exception;
use App\Service\LegacyWs\LegacyWsAuthentication;
use App\Service\LegacyWs\LegacyWsService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class LegacyWsController extends AbstractController
{
    /**
     * @Route("/seances.json", name="wd_connector")
     */
    public function addSitting(
        Request $request,
        LegacyWsService $wsService,
        LegacyWsAuthentication $legacyWsAuthentication,
        LoggerInterface $logger,
        MessageBusInterface $messageBus
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
            $structure = $legacyWsAuthentication->getStructureFromLegacyConnection($conn);
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

        $messageBus->dispatch(new UpdatedSitting($sitting->getId()));

        return $this->json([
            'success' => true,
            'code' => 'Seance.add.ok',
            'message' => 'La séance a bien été ajoutée.',
            'uuid' => $sitting->getId(),
        ]);
    }

    /**
     * @Route("/api300/ping", name="api_300_ping")
     * @Route("/Api300/ping", name="Api_300_ping")
     */
    public function ping(): Response
    {
        return new Response('ping');
    }

    /**
     * @Route("/api300/version", name="api_300_version")
     * @Route("/Api300/version", name="Api_300_version")
     */
    public function version(ParameterBagInterface $bag): Response
    {
        return new Response($bag->get('version'));
    }

    /**
     * post : [
     *          conn: "Connection"
     *          username : "secretaire"
     *          password : "idelibre"
     * return string :(success, error_database //database missing, error_user //auth error).
     *
     * @Route("/api300/check", name="api_300_check")
     * @Route("/Api300/check", name="Api_300_check")
     */
    public function check(Request $request, LegacyWsAuthentication $legacyWsAuthentication): Response
    {
        $legacyConnection = $request->request->get('conn');
        $plainPassword = $request->request->get('password');
        $username = $request->request->get('username');

        try {
            $structure = $legacyWsAuthentication->getStructureFromLegacyConnection($legacyConnection);
            $legacyWsAuthentication->loginUser($structure, $username . '@' . $structure->getSuffix(), $plainPassword);
        } catch (Http403Exception $e) {
            return new Response($e->getMessage(), $e->getCode());
        }

        return new Response('success');
    }
}
