<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Persistence\PersistenceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route('/api/v2/structures/{structureId}/users')]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class UserApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private PersistenceHelper $persistenceHelper
    ) {
    }

    #[Route('', name: 'get_all_users', methods: ['GET'])]
    public function getAll(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        UserRepository $userRepository
    ): JsonResponse {
        $users = $userRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($users, context: ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'user'])]
    public function getById(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['id' => 'id'])] User $user
    ): JsonResponse {
        return $this->json($user, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('', name: 'add_user', methods: ['POST'])]
    #[IsGranted('API_RELATION_USERS', subject: ['structure', 'data'])]
    public function add(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        ?array $data
    ): JsonResponse {
        /** @var User $user */
        $user = $this->denormalizer->denormalize($data, User::class, context: ['groups' => ['user:write', 'user:write:post'], 'normalize_relations' => true]);
        $user->setStructure($structure);

        $user->setPassword('No Password');
        if (!empty($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->persistenceHelper->validateAndPersist($user);

        return $this->json($user, status: 201, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('/{id}', name: 'edit_user', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'user'])]
    #[IsGranted('API_RELATION_USERS', subject: ['structure', 'data'])]
    public function update(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['id' => 'id'])] User $user,
        array $data
    ): JsonResponse {
        $context = ['object_to_populate' => $user, 'groups' => ['user:write'], 'normalize_relations' => true];

        /** @var User $updatedUser */
        $updatedUser = $this->denormalizer->denormalize($data, User::class, context: $context);

        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->persistenceHelper->validateAndPersist($updatedUser);

        return $this->json($user, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'user'])]
    public function delete(
        #[MapEntity(mapping: ['structureId' => 'id'])] Structure $structure,
        #[MapEntity(mapping: ['id' => 'id'])] User $user
    ): JsonResponse {
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}
