<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Repository\RoleRepository;
use App\Repository\TypeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v2/structure/{structureId}/roles')]
#[Route('/api/v2/roles')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
class RoleApiController extends AbstractController
{

    #[Route('/', name: 'get_all_roles', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        RoleRepository $roleRepository
    ): JsonResponse
    {
        $roles = $roleRepository->findAll();
        return $this->json($roles, context: ['groups' => 'role:read']);
    }

}
