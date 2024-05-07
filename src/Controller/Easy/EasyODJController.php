<?php

namespace App\Controller\Easy;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EasyODJController extends AbstractController
{
    #[Route(path: '/easy/sitting/{id}/odj', name: 'easy_odj_index')]
    #[IsGranted('ROLE_ACTOR')]   // todo check if is is sitting and sitting active !
    public function index(Sitting $sitting, ProjectRepository $projectRepository, ConvocationRepository $convocationRepository, OtherdocRepository $otherdocRepository): Response
    {
        $projects = $projectRepository->getProjectsBySitting($sitting);
        $otherDocs = $otherdocRepository->getOtherdocsBySitting($sitting);
        $convocation = $convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $this->getUser()]);

        $attendanceFormResponse =  $this->forward(AttendanceController::class . "::index", [
            'id' => $convocation->getId(),
        ]);


        return $this->render('easy/odj/index.html.twig', [
            'sitting' => $sitting,
            'projects' => $projects,
            'convocation' => $convocation,
            'otherDocs' => $otherDocs,
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
            'attendanceView' =>$attendanceFormResponse->getContent()
        ]);
    }


    #[Route(path: '/easy/sitting/{id}/AR', name: 'easy_odj_ar')]
    #[IsGranted('ROLE_ACTOR')]
    public function ar(Sitting $sitting, ConvocationRepository $convocationRepository): Response
    {
        $convocation = $convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $this->getUser()]);
        if (!$convocation) {
            throw new BadRequestHttpException("You are not convocated to this sitting");
        }

        if ($convocation->getIsRead()) {
            return $this->redirectToRoute('easy_odj_index', ['id' => $sitting->getId()]);
        }

        return $this->render('easy/odj/ar.html.twig', [
            'sitting' => $sitting,
            'convocation' => $convocation,
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);

    }


    #[Route(path: '/easy/sitting/{id}/ARBack', name: 'easy_odj_arBack')]
    #[IsGranted('ROLE_ACTOR')]  // TODO check if convocation belongs to user
    public function arBack(Convocation $convocation, ConvocationManager $convocationManager): Response
    {
        if (!$convocation->getIsRead()) {
            $convocationManager->markAsRead($convocation);
        }

        return $this->redirectToRoute('easy_odj_index', ['id' => $convocation->getSitting()->getId()]);
    }

}