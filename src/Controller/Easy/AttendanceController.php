<?php

namespace App\Controller\Easy;

use App\Entity\Convocation;
use App\Form\AttendanceType;
use App\Form\GroupStructureType;
use App\Service\Convocation\ConvocationAttendance;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AttendanceController extends AbstractController
{

    #[Route(path: '/attendance/{id}', name: 'easy_odj_index')]
    #[IsGranted('ROLE_ACTOR')]   // todo check if
    public function index(Convocation $convocation, Request $request, ConvocationManager $convocationManager): Response
    {
        $sitting = $convocation->getSitting();



        $form = $this->createForm(AttendanceType::class, null, [
            'isRemoteAllowed' => $sitting->getIsRemoteAllowed(),
            'isMandatorAllowed' => $sitting->isMandatorAllowed(),
            'convocation' => $convocation,
            'sitting' => $sitting,
            'toExclude' => [$this->getUser()],
            'deputyId' => $this->getUser()->getDeputy() ? $this->getUser()->getDeputy()->getId() : null
        ]);



        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $convocationAttendance = (new ConvocationAttendance())
                ->setAttendance($form->get('attendance')->getData());


            $convocationAttendance->setMandataire(null);
            if ($form->get('mandataire')->getData()) {
                $convocationAttendance->setMandataire($form->get('mandataire')->getData()->getId());
            }


            if (array_key_exists('deputyId', $form->createView()->children) && $form->get('attendance')->getData() === "deputy") {
                $convocationAttendance->setDeputyId($form->get('deputyId')->getData()->getId());
            }

            $convocationAttendance->setConvocationId($convocation->getId());

            $convocationManager->updateConvocationAttendances([$convocationAttendance]);

            return $this->redirectToRoute('easy_odj_index', ['id' => $convocation->getSitting()->getId()]);

        }

        return $this->render('easy/attendance/index.twig', [
            'user' => $this->getUser(),
            'sitting' => $sitting,
            'deputyId' => $this->getUser()->getDeputy() ? $this->getUser()->getDeputy()->getId() : null,
            'attendance' => $convocation->getAttendance(),
            'convocation' => $convocation,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
            'form' => $form->createView()
        ]);

    }

}