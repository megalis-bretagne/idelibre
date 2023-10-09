<?php

namespace App\Controller;

use App\Entity\AttendanceToken;
use App\Form\AttendanceType;
use App\Repository\ConvocationRepository;
use App\Repository\UserRepository;
use App\Service\Convocation\ConvocationAttendance;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AttendanceController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ConvocationManager $convocationManager,
    ) {
    }

    #[Route('/attendance/confirmation/{token}', name: 'app_attendance_confirmation')]
    public function confirmAttendanceFromEmail(AttendanceToken $attendanceToken, Request $request): Response
    {
        $convocation = $attendanceToken->getConvocation();
        $user = $attendanceToken->getConvocation()->getUser();
        $sitting = $attendanceToken->getConvocation()->getSitting();
        $deputyId = $user->getDeputy() ? $user->getDeputy()->getId() : null;

        $form = $this->createForm(AttendanceType::class, null, [
            'isRemoteAllowed' => $sitting->getIsRemoteAllowed(),
            'convocation' => $convocation,
            'sitting' => $sitting,
            'toExclude' => [$user],
            'deputyId' => $deputyId
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $convocationAttendance = (new ConvocationAttendance())
                ->setAttendance($form->get('attendance')->getData());
            !$form->get('mandataire')->getData() ?
                $convocationAttendance->setMandataire(null) :
                $convocationAttendance->setMandataire($form->get('mandataire')->getData()->getId());
            !$form->get('deputyId')->getData() ?
                $convocationAttendance->setDeputyId(null) :
                $convocationAttendance->setDeputyId($form->get('deputyId')->getData()->getId());
            $convocationAttendance->setConvocationId($attendanceToken->getConvocation()->getId());

            $this->convocationManager->updateConvocationAttendances([$convocationAttendance]);

            $this->addFlash('success', 'Présence enregistrée');

            return $this->redirectToRoute('app_attendance_redirect', ['token' => $attendanceToken->getToken()]);
        }

        return $this->render('confirm_attendance/confirm.html.twig', [
            'user' => $user,
            'sitting' => $sitting,
            'deputyId' => $deputyId,
            'convocation' => $convocation,
            'token' => $attendanceToken->getToken(),
            'attendance' => $attendanceToken->getConvocation()->getAttendance(),
            'timezone' => $attendanceToken->getConvocation()->getSitting()->getStructure()->getTimezone()->getName(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/attendance/redirect/{token}', name: 'app_attendance_redirect')]
    public function attendanceRedirect(AttendanceToken $attendanceToken): Response
    {
        return $this->render('confirm_attendance/attendance_redirect.html.twig', [
            'token' => $attendanceToken->getToken(),
            'attendance' => $attendanceToken->getConvocation()->getAttendance(),
            'convocation' => $attendanceToken->getConvocation(),
        ]);
    }

    #[Route('/attendance/{token}/actors', name: 'attendance_actors_list')]
    public function getActorsList(AttendanceToken $attendanceToken): Response
    {
        $user = $attendanceToken->getConvocation()->getUser();
        $user ? $toExclude[] = $user : $toExclude = [];
        $actors = $this->userRepository->findActorsInSittingWithExclusion($attendanceToken->getConvocation()->getSitting(), $toExclude)->getQuery()->getResult();

        return $this->render('confirm_attendance/includes/_list_actors.html.twig', [
            "availables" => $actors
        ]);
    }

    #[Route('/attendance/{token}/deputy', name: 'attendance_deputies_list')]
    public function getDeputiesList(AttendanceToken $attendanceToken): Response
    {
        $structure = $attendanceToken->getConvocation()->getSitting()->getStructure();
        $user = $attendanceToken->getConvocation()->getUser();

        $user ? $toExclude[] = $user : $toExclude = [];
        $deputies = $this->userRepository->findDeputiesWithNoAssociation($structure, $toExclude)->getQuery()->getResult();

        return $this->render('confirm_attendance/includes/_list_actors.html.twig', [
            "availables" => $deputies
        ]);
    }
}
