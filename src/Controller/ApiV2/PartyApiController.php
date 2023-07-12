<?php

namespace App\Controller\ApiV2;

use App\Entity\Party;
use App\Entity\Structure;
use App\Repository\PartyRepository;
use App\Service\Persistence\PersistenceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;


/**
 * body {
 *   "name": string,
 * }.
 */
#[Route('/api/v2/structures/{structureId}/parties')]
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
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        PartyRepository $partyRepository
    ): JsonResponse {
        $parties = $partyRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($parties, context: ['groups' => 'party:read']);
    }

    #[Route('/{id}', name: 'get_party', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function getById(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        Party $party
    ): JsonResponse {
        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('', name: 'add_party', methods: ['POST'])]
    public function add(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        array $data
    ): JsonResponse
    {
        /** @var Party $party */
        $party = $this->denormalizer->denormalize($data, Party::class, context: ['groups' => ['party:write']]);

        $party->setStructure($structure);

        $this->persistenceHelper->validateAndPersist($party);

        return $this->json($party, status: 201, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'edit_party', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function update(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['id' => 'id'])] Party $party, array $data)
    : JsonResponse
    {
        $context = ['object_to_populate' => $party, 'groups' => ['party:write']];

        /** @var Party $updatedParty */
        $updatedParty = $this->denormalizer->denormalize($data, Party::class, context: $context);

        $this->persistenceHelper->validateAndPersist($updatedParty);

        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'delete_party', methods: ['DELETE'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'party'])]
    public function delete(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['id' => 'id'])]Party $party
    ): JsonResponse
    {
        $this->em->remove($party);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}
