<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route('/api/v2/structures/{structureId}/users')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class UserApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('', name: 'get_all_users', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        UserRepository $userRepository
    ): JsonResponse {
        $users = $userRepository->findByStructure($structure)->getQuery()->getResult();

        return $this->json($users, context: ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure','user'])]
    public function getById(
        Structure $structure,
        User $user
    ): JsonResponse {
        return $this->json($user, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('', name: 'add_user', methods: ['POST'])]
    #[IsGranted('API_RELATION_USERS', subject: ['structure', 'data'])]
    public function add(Structure $structure, ?array $data): JsonResponse
    {
        /** @var User $user */
        $user = $this->denormalizer->denormalize($data, User::class, context: ['groups' => ['user:write', 'user:write:post'], 'normalize_relations' => true]);
        $user->setStructure($structure);

        $user->setPassword('No Password');
        if (!empty($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user, status: 201, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('/{id}', name: 'edit_user', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure','user'])]
    #[IsGranted('API_RELATION_USERS', subject: ['structure','data'])]
    public function update(Structure $structure, User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $context = ['object_to_populate' => $user, 'groups' => ['user:write']];

        /** @var User $updatedUser */
        $updatedUser = $this->denormalizer->denormalize($data, User::class, context: $context);

        if (!isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->em->persist($updatedUser);
        $this->em->flush();

        return $this->json($user, context: ['groups' => ['user:detail', 'user:read']]);
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'user'])]
    public function delete(Structure $structure, User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(null, status: 204);
    }
}
