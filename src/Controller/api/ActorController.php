<?php


namespace App\Controller\api;

use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
