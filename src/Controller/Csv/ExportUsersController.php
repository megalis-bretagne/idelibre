<?php

namespace App\Controller\Csv;

use App\Entity\Structure;
use App\Service\Csv\CsvException;
use App\Service\Csv\ExportUsersCsv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExportUsersController extends AbstractController
{
    public function __construct(
        private readonly ExportUsersCsv $exportUsersCsv,
    )
    {
    }

    /**
     * @throws CsvException
     */
    #[Route('/export/csv/structure/{id}/users', name: 'export_csv_users', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function exportCsvUsers(Structure $structure): Response
    {

        $response = new BinaryFileResponse($this->exportUsersCsv->generate($structure->getId()));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $structure->getName() . '_utilisateurs.csv'
        );
        return $response;
    }

    #[Route('/export/csv/group/{id}/users', name: 'export_csv_users_group', methods: ['GET'])]
    #[isGranted('ROLE_GROUP_ADMIN')]
    public function exportCsvUsersFromGroup(): Response
    {

        return $response;
    }
}
