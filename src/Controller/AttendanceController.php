<?php

namespace App\Controller;

use App\Entity\AttendanceToken;
use App\Form\AttendanceType;
use App\Service\Convocation\ConvocationAttendance;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AttendanceController extends AbstractController
{
    #[Route('/attendance/confirmation/{token}', name: 'app_attendance_confirmation')]
    public function confirmAttendanceFromEmail(AttendanceToken $attendanceToken, Request $request, ConvocationManager $convocationManager): Response
    {
        $sitting = $attendanceToken->getConvocation()->getSitting();
        $form = $this->createForm(AttendanceType::class, null, [
            'isRemoteAllowed' => $sitting->getIsRemoteAllowed(),
            'convocation' => $attendanceToken->getConvocation(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $convocationAttendance = (new ConvocationAttendance())
                ->setAttendance($form->get('attendance')->getData())
                ->setDeputy($form->get('deputy')->getData())
                ->setConvocationId($attendanceToken->getConvocation()->getId());

            $convocationManager->updateConvocationAttendances([$convocationAttendance]);

            $this->addFlash('success', 'Présence enregistrée');

            return $this->redirectToRoute('app_attendance_redirect', ['token' => $attendanceToken->getToken()]);
        }

        return $this->render('confirm_attendance/confirm.html.twig', [
            'token' => $attendanceToken->getToken(),
            'user' => $attendanceToken->getConvocation()->getUser(),
            'sitting' => $attendanceToken->getConvocation()->getSitting(),
            'timezone' => $attendanceToken->getConvocation()->getSitting()->getStructure()->getTimezone()->getName(),
            'attendance' => $attendanceToken->getConvocation()->getAttendance(),
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
}
