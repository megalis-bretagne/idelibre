<?php

namespace App\Controller;

use App\Service\Statistic\RoleByStructureStatisticCsvGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class StatisticController extends AbstractController
{
    #[Route('statistics/user')]
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
}
