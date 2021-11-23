<?php

namespace App\Controller\ApiV2;

use App\Entity\Party;
use App\Entity\Structure;
use App\Repository\PartyRepository;
use App\Service\Persistence\PersistenceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * body {
 *   "name": string,
 * }.
 */
#[Route('/api/v2/structures/{structureId}/parties')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class PartyApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
        private PersistenceHelper $persistenceHelper
    ) {
    }

    #[Route('', name: 'get_all_parties', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        PartyRepository $partyRepository
    ): JsonResponse {
        $parties = $partyRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($parties, context: ['groups' => 'party:read']);
    }

    #[Route('/{id}', name: 'get_party', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function getById(
        Structure $structure,
        Party $party
    ): JsonResponse {
        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('', name: 'add_party', methods: ['POST'])]
    public function add(Structure $structure, array $data): JsonResponse
    {
        /** @var Party $party */
        $party = $this->denormalizer->denormalize($data, Party::class, context:['groups' => ['party:write']]);

        $party->setStructure($structure);

        $this->persistenceHelper->validateAndPersist($party);

        return $this->json($party, status: 201, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'edit_party', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function update(Structure $structure, Party $party, array $data): JsonResponse
    {
        $context = ['object_to_populate' => $party, 'groups' => ['party:write']];

        /** @var Party $updatedParty */
        $updatedParty = $this->denormalizer->denormalize($data, Party::class, context: $context);

        $this->persistenceHelper->validateAndPersist($updatedParty);

        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'delete_party', methods: ['DELETE'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function delete(Structure $structure, Party $party): JsonResponse
    {
        $this->em->remove($party);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}