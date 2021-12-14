<?php

namespace App\Controller\Api32;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class TypesController extends AbstractController
{
    /**
     * @deprecated
     * @Route("/api/v1/types", name="list_types", methods={"GET"})
     */
    public function listTypeSittings(
        Request $request,
        VerifyToken $verifyToken,
        TypeRepository $typeRepository
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $types = $typeRepository->findBy(['structure' => $structure]);

        $formattedTypes = [];
        foreach ($types as $type) {
            $formattedTypes[] = ['id' => $type->getId(), 'name' => $type->getName()];
        }

        return $this->json($formattedTypes);
    }

    /**
     * @deprecated
     * @Route("/api/v1/types", name="create_types", methods={"POST"})
     */
    public function createTypeSittings(
        Request $request,
        VerifyToken $verifyToken,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $structure = $verifyToken->validate($request);

        $content = json_decode($request->getContent(), true);
        $typeName = $content['name'];
        $type = new Type();
        $type->setName($typeName)
            ->setStructure($structure);

        $entityManager->persist($type);
        $entityManager->flush();

        return $this->json(['typeId' => $type->getId()], 201);
    }
}
