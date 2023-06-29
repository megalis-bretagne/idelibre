<?php

namespace App\Controller;

use App\Service\Statistic\RoleByStructureStatisticCsvGenerator;
use App\Service\Statistic\SittingByStructureStatisticCsvGenerator;
use App\Sidebar\Annotation\Sidebar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['platform-nav', 'statistic-nav'])]
class StatisticController extends AbstractController
{
    #[Route(path: '/statistic', name: 'statistic_index')]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function index(): Response
    {
        return $this->render('statistic/statistic_index.html.twig', [
        ]);
    }

    #[Route('statistic/user', name: 'statistic_user')]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function countUsers(RoleByStructureStatisticCsvGenerator $statisticCsvGenerator): Response
    {
        $response = new BinaryFileResponse($statisticCsvGenerator->generate());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'user_structure_rapport.csv'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route('statistic/sitting', name: 'statistic_sitting')]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function sittingsInfoAfter(Request $request, SittingByStructureStatisticCsvGenerator $statisticCsvGenerator): Response
    {
        $months = (int) $request->get('months') ?? 3;
        $response = new BinaryFileResponse($statisticCsvGenerator->generate($months));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'convocation_structure_rapport.csv'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
