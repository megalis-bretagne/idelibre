<?php

namespace App\Controller\api;

use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Repository\UserRepository;
use App\Service\Convocation\ConvocationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/actors", name="api_actor_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function getActors(UserRepository $userRepository): JsonResponse
    {
        return $this->json(
            $userRepository->findActorsByStructure($this->getUser()->getStructure())->getQuery()->getResult(),
            200,
            [],
            ['groups' => ['user']]
        );
    }

    /**
     * @Route("/api/users/sittings/{id}", name="api_user_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getUsersInSitting(Sitting $sitting, UserRepository $userRepository): JsonResponse
    {
        return $this->json(
            [
                'actors' => $userRepository->findActorsInSitting($sitting)->getQuery()->getResult(),
                'employees' => $userRepository->findInvitableEmployeesInSitting($sitting)->getQuery()->getResult(),
                'guests' => $userRepository->findGuestsInSitting($sitting)->getQuery()->getResult(),
            ],
            200,
            [],
            ['groups' => ['user']]
        );
    }

    /**
     * @Route("/api/users/sittings/{id}/not", name="api_user_not_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getUsersNotInSitting(Sitting $sitting, UserRepository $userRepository): JsonResponse
    {
        return $this->json(
            [
                'actors' => $userRepository->findActorsNotInSitting($sitting, $sitting->getStructure())->getQuery()->getResult(),
                'employees' => $userRepository->findInvitableEmployeesNotInSitting($sitting, $sitting->getStructure())->getQuery()->getResult(),
                'guests' => $userRepository->findGuestNotInSitting($sitting, $sitting->getStructure())->getQuery()->getResult(),
            ],
            200,
            [],
            ['groups' => ['user']]
        );
    }

    /**
     * @Route("/api/users/sittings/{id}", name="api_user_sitting_modify", methods={"PUT"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function updateActorsInSitting(Sitting $sitting, Request $request, ConvocationManager $convocationManager, UserRepository $userRepository, ConvocationRepository $convocationRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $convocationManager->addConvocations($userRepository->findBy(['id' => [...$data['addedActors'], ...$data['addedEmployees'], ...$data['addedGuests']]]), $sitting);
        $convocationManager->deleteConvocationsNotSent($convocationRepository->getConvocationsBySittingAndActorIds($sitting, $data['removedUsers']));

        return $this->json(
            ['ok' => true]
        );
    }

    /**
     * @Route("/api/users/sittings/{id}/sent", name="api_users_sitting_sent", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getUSerConvocationSent(Sitting $sitting, UserRepository $userRepository): JsonResponse
    {
        return $this->json([
            'actors' => $userRepository->findActorIdsConvocationSent($sitting),
            'employees' => $userRepository->findInvitableEmployeesIdsConvocationSent($sitting),
            'guests' => $userRepository->findGuestsIdsConvocationSent($sitting),
        ]);
    }
}
