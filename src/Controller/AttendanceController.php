<?php

namespace App\Controller;

use App\Entity\AttendanceToken;
use App\Entity\Convocation;
use App\Service\Convocation\ConvocationAttendance;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AttendanceController extends AbstractController
{
    #[Route('/attendance/confirmation/{token}', name: 'app_attendance_confirmation')]
    public function index(AttendanceToken $attendanceToken, Request $request, ConvocationManager $convocationManager): Response
    {
        if ($request->isMethod('POST')) {
            $attendance = $request->get('attendance');
            $deputy = Convocation::PRESENT === $attendance ? null : $request->get('deputy');
            $isRemote = Convocation::ABSENT === $attendance ? false : $request->get('isRemote');

            $convocationAttendance = (new ConvocationAttendance())
               ->setAttendance('present' === $attendance ? Convocation::PRESENT : Convocation::ABSENT)
               ->setDeputy($deputy)
               ->setIsRemote($isRemote)
               ->setConvocationId($attendanceToken->getConvocation()->getId());

            $convocationManager->updateConvocationAttendances([$convocationAttendance]);

            $this->addFlash('success', 'Présence enregistrée');

            return $this->redirectToRoute('app_attendance_redirect', ['token' => $attendanceToken->getToken()]);
        }

        return $this->render('confirm_attendance/index.html.twig', [
            'token' => $attendanceToken->getToken(),
            'user' => $attendanceToken->getConvocation()->getUser(),
            'sitting' => $attendanceToken->getConvocation()->getSitting(),
            'timezone' => $attendanceToken->getConvocation()->getSitting()->getStructure()->getTimezone()->getName(),
            'attendance' => $attendanceToken->getConvocation()->getAttendance(),
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
