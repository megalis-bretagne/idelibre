<?php

namespace App\Controller\api;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Requirements\Is;
use App\Service\Connector\ComelusConnectorManager;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\Connector\LsvoteSittingCreationException;
use App\Service\Convocation\ConvocationManager;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\NotificationService;
use App\Service\Util\Converter;
use Doctrine\DBAL\ConnectionException;
use PHPUnit\Util\Json;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SittingController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ConvocationManager $convocationManager,
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws EmailNotSendException
     */
    #[Route(path: '/api/sittings/{id}/sendConvocations', name: 'api_convocations_send', methods: ['POST'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function sendConvocations(Sitting $sitting, Request $request): JsonResponse
    {
        $this->convocationManager->sendAllConvocations($sitting, $request->get('userProfile'));

        return $this->json(['success' => true]);
    }

    #[Route(path: '/api/sittings/{id}/notifyAgain', name: 'api_sitting_notify_again', methods: ['POST'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function notifyAgain(Sitting $sitting, NotificationService $notificationService, Request $request): JsonResponse
    {
        $msg = $request->toArray();
        $notificationService->reNotify($sitting, $msg['object'], $msg['content']);

        return $this->json(['success' => true]);
    }

    #[Route(path: '/api/sittings/{id}', name: 'api_sitting_details', requirements: ['id' => Is::UUID], methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function getSitting(Sitting $sitting): JsonResponse
    {
        return $this->json($sitting, 200, [], ['groups' => ['sitting']]);
    }

    #[Route(path: '/api/sittings/{id}/sendComelus', name: 'api_sitting_send_comelus', methods: ['POST'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function sendComelus(Sitting $sitting, ComelusConnectorManager $comelusConnectorManager): JsonResponse
    {
        $comelusId = $comelusConnectorManager->sendComelus($sitting);

        return $this->json(['comelusId' => $comelusId]);
    }


    #[Route(path: '/api/sittings/{id}/sendLsvote', name: 'api_sitting_sendLsvote', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function sendToLsvote(Sitting $sitting, LsvoteConnectorManager $lsvoteConnectorManager): Response
    {
        try {
            if ($sitting->getLsvoteSitting()?->getLsvoteSittingId()) {
                $lsvoteId = $lsvoteConnectorManager->editLsvoteSitting($sitting);

                return $this->json(['lsvoteId' => $lsvoteId]);
            }

            $lsvoteId = $lsvoteConnectorManager->createSitting($sitting);

            return $this->json(['lsvoteId' => $lsvoteId]);
        } catch (LsvoteSittingCreationException $e) {
            return $this->json($e->getMessage(), 400);
        }
    }


    #[Route(path: '/api/sittings/sittingMaxSize', name: 'api_sitting_max_size', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function getSittingMaxSize(ParameterBagInterface $bag): jsonResponse
    {
        return $this->json(['sittingMaxSize' => $bag->get('max_sitting_size')]);
    }

    #[Route(path: '/api/sittings/maxGenerationSize', name: 'api_max_generation_size_sitting', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function getMaxSittingSizeForGeneration(ParameterBagInterface $bag): jsonResponse
    {
        return $this->json(['maxGenerationSize' => $bag->get('maximum_size_pdf_zip_generation')]);
    }


    #[Route(path: '/api/sittings/fileMaxSize', name: 'api_sitting_file_maxSize', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function getMaxFileSizeForGeneration(Converter $converter): jsonResponse
    {
        return $this->json(['fileMaxSize' => $converter->bytesConverter(ini_get('upload_max_filesize'))]);
    }

    #[Route(path: '/api/sittings/{id}/timezone', name: 'api_sitting_timezone', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function getCurrentStructureSittingTimezone(Sitting $sitting): jsonResponse
    {
        return $this->json(['timezone' => $sitting->getStructure()->getTimezone()->getName()]);
    }

    #[Route(path: '/api/sittings/{id}/actors')]
    public function getActor(Sitting $sitting): JsonResponse
    {
        return $this->json([
            "actors" => $this->userRepository->findActorsInSittingWithExclusion($sitting, [])->getQuery()->getResult(),
        ], 200, [], ['groups' => ['user']]);
    }

    #[Route(path: '/api/sittings/{id}/countNotAnswered', name: 'api_sitting_count_attendance', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function countAttendance(Sitting $sitting): JsonResponse
    {
        return $this->json(["notAnswered" => $this->convocationManager->countConvocationNotanswered($sitting->getConvocations())]);
    }
}
