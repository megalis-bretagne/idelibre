<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Service\Csv\ExportUsersCsv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExportUsersController extends AbstractController
{
    public function __construct(
        private readonly ExportUsersCsv $exportUsersCsv,
    )
    {
    }

    #[Route('/export/csv/structure/{id}/users', name: 'export_csv_users')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function exportCsvUsers(Structure $structure): Response
    {
        $this->exportUsersCsv->execute($structure->getId());
        $this->addFlash('success', 'Export des utilisateurs effectué');
        return $this->redirectToRoute('user_index');
    }
}
