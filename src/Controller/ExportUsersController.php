<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Service\Csv\ExportUsersCsv;
use App\Service\Util\FileUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExportUsersController extends AbstractController
{
    public function __construct(
        private readonly ExportUsersCsv $exportUsersCsv,
        private readonly FileUtil $fileUtil,
    )
    {
    }

    #[Route('/export/csv/structure/{id}/users', name: 'export_csv_users')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function exportCsvUsers(Structure $structure): Response
    {
        $response = new BinaryFileResponse($this->exportUsersCsv->generate($structure));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'user.csv'
//            $this->fileUtil->sanitizeName($structure->getName()) . '_users.csv'
        );
        $response->deleteFileAfterSend();

        return $response;
    }
}
