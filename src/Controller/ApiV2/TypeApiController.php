<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * body {
 * "name":"string",
 * "isSms":bool,
 * "isComelus":bool,
 * "reminder":{"duration":<60, 90, 120, 180, 240, 300>,"isActive":bool},
 * 'associatedUsers':[{userIds}]
 * }.
 */
#[Route('/api/v2/structures/{structureId}/types')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
class TypeApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
        private UserManager $userManager
    ) {
    }

    #[Route('', name: 'get_all_types', methods: ['GET'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function getAll(Structure $structure, TypeRepository $typeRepository): JsonResponse
    {
        $types = $typeRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($types, context: ['groups' => 'type:read']);
    }

    #[Route('/{id}', name: 'get_type', methods: ['GET'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function getById(Structure $structure, Type $type): JsonResponse
    {
        return $this->json($type, context: ['groups' => ['type:detail', 'type:read']]);
    }

    #[Route('', name: 'add_type', methods: ['POST'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function add(Structure $structure, array $data): JsonResponse
    {
        $type = $this->denormalizer->denormalize($data, Type::class, context: ['groups' => ['type:write']]);
        $type->setStructure($structure);

        $this->userManager->associateTypeToUserIds($type, $data['associatedUsers'] ?? null);

        $this->em->persist($type);
        $this->em->flush();

        return $this->json($type, status: 201, context: ['groups' => ['type:detail', 'type:read']]);
    }

    #[Route('/{id}', name: 'edit_type', methods: ['PUT'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function update(Structure $structure, Type $type, array $data): JsonResponse
    {
        $context = ['object_to_populate' => $type, 'groups' => ['type:write']];

        /** @var Type $type */
        $updatedType = $this->denormalizer->denormalize($data, Type::class, context: $context);
        $this->userManager->associateTypeToUserIds($type, $data['associatedUsers'] ?? null);

        $this->em->persist($updatedType);
        $this->em->flush();

        return $this->json($type, context: ['groups' => ['type:detail', 'type:read']]);
    }

    #[Route('/{id}', name: 'delete_type', methods: ['DELETE'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function delete(Structure $structure, Type $type): JsonResponse
    {
        $this->em->remove($type);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}
