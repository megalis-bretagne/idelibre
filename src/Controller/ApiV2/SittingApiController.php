<?php

namespace App\Controller\ApiV2;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\ConvocationRepository;
use App\Repository\ProjectRepository;
use App\Repository\SittingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v2/structures/{structureId}/sittings')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class SittingApiController extends AbstractController
{
    #[Route('', name: 'get_all_sittings', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        Request $request,
        SittingRepository $sittingRepository
    ): JsonResponse {
        $sittings = $sittingRepository->findByStructure($structure, null, $request->query->get('status'))
        ->getQuery()->getResult();

        return $this->json($sittings, context: ['groups' => 'sitting:read']);
    }

    #[Route('/{id}', name: 'get_one_sitting', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getById(
        Structure $structure,
        Sitting $sitting
    ): JsonResponse {
        return $this->json($sitting, context: ['groups' => ['sitting:detail', 'sitting:read']]);
    }

    #[Route('/{sittingId}/convocations', name: 'get_all_convocations_by_sitting', methods: ['GET'])]
    #[ParamConverter('sitting', class: Sitting::class, options: ['id' => 'sittingId'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getAllConvocations(
        Structure $structure,
        Sitting $sitting,
        ConvocationRepository $convocationRepository
    ): JsonResponse {
        $convocations = $convocationRepository->getConvocationsWithUserBySitting($sitting);

        return $this->json($convocations, context: ['groups' => 'convocation:read']);
    }

    #[Route('/{sittingId}/projects', name: 'get_all_projects_by_sitting', methods: ['GET'])]
    #[ParamConverter('sitting', class: Sitting::class, options: ['id' => 'sittingId'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getAllProjects(
        Structure $structure,
        Sitting $sitting,
        ProjectRepository $projectRepository
    ): JsonResponse {
        $projects = $projectRepository->getProjectsBySitting($sitting);

        return $this->json($projects, context: ['groups' => 'project:read']);
    }
}
