<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Repository\RoleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v2/structures/{structureId}/roles')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class RoleApiController extends AbstractController
{
    #[Route('', name: 'get_all_roles', methods: ['GET'])]
    public function getAll(
        Structure      $structure,
        RoleRepository $roleRepository
    ): JsonResponse
    {
        $roles = $roleRepository->findInStructureQueryBuilder()->getQuery()->getResult();
        return $this->json($roles, context: ['groups' => 'role:read']);
    }
}
