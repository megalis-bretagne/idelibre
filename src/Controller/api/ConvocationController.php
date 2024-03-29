<?php

namespace App\Controller\api;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Service\Convocation\ConvocationAttendance;
use App\Service\Convocation\ConvocationManager;
use App\Service\Email\EmailData;
use App\Service\EmailTemplate\EmailGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConvocationController extends AbstractController
{
    #[Route(path: '/api/convocations/{id}', name: 'api_convocation_sitting', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
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
            ['groups' => ['convocation', 'user', 'userAssociated', 'party:read']]
        );
    }

    #[Route(path: '/api/convocations/{id}/send', name: 'api_convocation_send', methods: ['POST'])]
    #[IsGranted('MANAGE_CONVOCATIONS', subject: 'convocation')]
    public function sendConvocation(Convocation $convocation, ConvocationManager $convocationManager): JsonResponse
    {
        $convocationManager->sendConvocation($convocation);

        return $this->json($convocation, 200, [], ['groups' => ['convocation', 'user', 'party:read']]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(path: '/api/convocations/attendance', name: 'api_convocation_attendance', methods: ['POST', 'PUT'])]
    #[IsGranted('MANAGE_ATTENDANCE', subject: 'request')]
    public function setAttendance(ConvocationManager $convocationManager, Request $request, DenormalizerInterface $denormalizer): JsonResponse
    {
        $convocationAttendances = $denormalizer->denormalize($request->toArray(), ConvocationAttendance::class . '[]', context: ['normalize_relations' => true]);
        $convocationManager->updateConvocationAttendances($convocationAttendances);

        return $this->json(['success' => 'true']);
    }

    #[Route(path: '/api/convocations/previewForSecretary/{id}', name: 'api_convocation_preview_for_secretary', methods: ['GET'])]
    #[IsGranted('MANAGE_CONVOCATIONS', subject: 'convocation')]
    public function iframePreviewForSecretary(Convocation $convocation, EmailGenerator $generator): Response
    {
        $convocation->setCategory('convocation');
        $emailData = $generator->generateFromTemplateAndConvocation($convocation->getSitting()->getType()->getEmailTemplate(), $convocation);
        $content = $emailData->getContent();
        if (EmailData::FORMAT_TEXT === $emailData->getFormat()) {
            $content = htmlspecialchars($content);
            $content = nl2br($content);
        }

        return new Response($content);
    }

    #[Route(path: '/api/convocations/previewForSecretaryOther/{id}', name: 'api_convocation_preview_for_secretary_other', methods: ['GET'])]
    #[IsGranted('MANAGE_CONVOCATIONS', subject: 'convocation')]
    public function iframePreviewForSecretaryOther(Convocation $convocation, EmailGenerator $generator): Response
    {
        $convocation->setCategory('invitation');
        $emailData = $generator->generateFromTemplateAndConvocation($convocation->getSitting()->getType()->getEmailTemplate(), $convocation);
        $content = $emailData->getContent();
        if (EmailData::FORMAT_TEXT === $emailData->getFormat()) {
            $content = htmlspecialchars($content);
            $content = nl2br($content);
        }

        return new Response($content);
    }
}
