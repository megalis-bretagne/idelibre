<?php

namespace App\Controller\ApiV2;

use App\Entity\Party;
use App\Entity\Structure;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route('/api/v2/structure/{structureId}/parties')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
class PartyApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'get_all_parties', methods: ['GET'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function getAll(
        Structure $structure,
        PartyRepository $partyRepository
    ): JsonResponse {
        $parties = $partyRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($parties, context: ['groups' => 'party:read']);
    }

    #[Route('/{id}', name: 'get_party', methods: ['GET'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function getById(
        Structure $structure,
        Party $party
    ): JsonResponse {
        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('', name: 'add_party', methods: ['POST'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function add(Structure $structure, array $data): JsonResponse
    {
        /** @var Party $party */
        $party = $this->denormalizer->denormalize($data, Party::class, context:['groups' => ['party:write']]);

        $party->setStructure($structure);

        $this->em->persist($party);
        $this->em->flush();

        return $this->json($party, status: 201, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'edit_party', methods: ['PUT'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function edit(Structure $structure, Party $party, array $data): JsonResponse
    {
        $context = ['object_to_populate' => $party, 'groups' => ['party:write']];

        /** @var Party $updatedParty */
        $updatedParty = $this->denormalizer->denormalize($data, Party::class, context: $context);

        $this->em->persist($updatedParty);
        $this->em->flush();

        return $this->json($party, context: ['groups' => ['party:detail', 'party:read']]);
    }

    #[Route('/{id}', name: 'delete_party', methods: ['DELETE'])]
    #[IsGranted('API_MY_STRUCTURE', subject: 'structure')]
    public function delete(Structure $structure, Party $party): JsonResponse
    {
        $this->em->remove($party);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}
