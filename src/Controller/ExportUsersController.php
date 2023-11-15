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

    #[Route('/export/pdf/users', name: 'export_pdf_users')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function export_users(Structure $structure, ExportUsersCsv $exportUsersCsv, FileUtil $fileUtil): Response
    {
        dd($structure);
        $response = new BinaryFileResponse($exportUsersCsv->generate($structure));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileUtil->sanitizeName($structure->getName()) . '_users.csv'
        );
        $response->deleteFileAfterSend();

        return $response;
    }
}
