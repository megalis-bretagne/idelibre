<?php

namespace App\Controller\Csv;

use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Service\Csv\ExportUsersCsv;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExportUsersController extends AbstractController
{
    public function __construct(
        private readonly ExportUsersCsv $exportUsersCsv,
    ) {
    }


    /**
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
    #[Route('/export/csv/structure/users', name: 'export_csv_users', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function exportCsvUsers(): Response
    {
        $structure = $this->getUser()->getStructure();
        $response = new BinaryFileResponse($this->exportUsersCsv->exportStructureUsers($structure));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $structure->getName() . '.csv'
        );

        $response->deleteFileAfterSend();

        return $response;
    }

    /**
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
    #[Route('/export/csv/group/{id}/users', name: 'export_csv_users_group', methods: ['GET'])]
    #[isGranted('MANAGE_GROUPS', subject: 'group')]
    public function exportCsvUsersFromGroup(Group $group): Response
    {
        $response = new BinaryFileResponse($this->exportUsersCsv->exportGroupUsers($group));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $group->getName() . '.zip'
        );

        $response->deleteFileAfterSend();

        return $response;
    }
}
