<?php


namespace App\Controller\api;

use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Repository\UserRepository;
use App\Service\Convocation\ConvocationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActorController extends AbstractController
{
    /**
     * @Route("/api/actors", name="api_actor_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function getActors(UserRepository $userRepository): Response
    {
        return $this->json(
            $userRepository->findActorByStructure($this->getUser()->getStructure())->getQuery()->getResult(),
            200,
            [],
            ['groups' => ['user']]
        );
    }


    /**
     * @Route("/api/actors/sittings/{id}", name="api_actor_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getActorsInSitting(Sitting $sitting, UserRepository $userRepository): Response
    {
        return $this->json(
            $userRepository->findActorsInSitting($sitting, $sitting->getStructure())->getQuery()->getResult(),
            200,
            [],
            ['groups' => ['user']]
        );
    }


    /**
     * @Route("/api/actors/sittings/{id}/not", name="api_actor_not_sitting", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getActorsNotInSitting(Sitting $sitting, UserRepository $userRepository): Response
    {
        return $this->json(
            $userRepository->findActorsNotInSitting($sitting, $sitting->getStructure())->getQuery()->getResult(),
            200,
            [],
            ['groups' => ['user']]
        );
    }


    /**
     * @Route("/api/actors/sittings/{id}", name="api_actor_sitting_modify", methods={"PUT"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function updateActorsInSitting(Sitting $sitting, Request $request, ConvocationManager $convocationManager, UserRepository $userRepository, ConvocationRepository $convocationRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $convocationManager->addConvocations($userRepository->findBy(['id' => $data['addedActors']]), $sitting);
        $convocationManager->removeConvocations($convocationRepository->getConvocationsBySittingAndActorIds($sitting, $data['removedActors']));

        return $this->json(
            ['ok' => true]
        );
    }

    /**
     * @Route("/api/actors/sittings/{id}/sent", name="api_actors_sitting_sent", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getActorsConvocationSent(Sitting $sitting, UserRepository $userRepository){
        return $this->json($userRepository->findActorIdsConvocationSent($sitting));
    }
}
