<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Repository\RoleRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v2/structures/{structureId}/roles')]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class RoleApiController extends AbstractController
{
    #[Route('', name: 'get_all_roles', methods: ['GET'])]
    public function getAll(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        RoleRepository $roleRepository
    ): JsonResponse {
        $roles = $roleRepository->findInStructureQueryBuilder()->getQuery()->getResult();

        return $this->json($roles, context: ['groups' => 'role:read']);
    }
}
