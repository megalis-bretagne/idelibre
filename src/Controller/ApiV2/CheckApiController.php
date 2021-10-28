<?php

namespace App\Controller\ApiV2;

use App\Entity\ApiUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v2')]
class CheckApiController extends AbstractController
{
    #[Route('/ping', name: 'api_ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json(['message' => 'success']);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted("ROLE_API")]
    public function me(): JsonResponse
    {
        /** @var ApiUser $userApi */
        $apiUser = $this->getUser();

        return $this->json(
            [
                'name' => $apiUser->getName(),
                'structure' => [
                    'id' => $apiUser->getStructure()->getId(),
                    'name' => $apiUser->getStructure()->getName()
                    ],
                'role' => $apiUser->getRoles()
           ]);
    }

}
