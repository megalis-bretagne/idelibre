<?php

namespace App\Controller\Easy;

use App\Entity\Convocation;
use App\Entity\File;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EasyViewerController extends AbstractController
{
    #[Route(path: '/easy/viewer/sitting/{sittingId}/file/{fileId}', name: 'easy_viewer')]
    #[IsGranted('VISUALIZE_SITTING', subject: 'sitting')]
    #[IsGranted('DOWNLOAD_FILES', subject: 'file')] // TODO check permission
    public function viewDocument(
        #[MapEntity(mapping: ['sittingId' => 'id'])] Sitting $sitting,
        #[MapEntity(mapping: ['fileId' => 'id'])] File       $file,
        ProjectRepository                                    $projectRepository,
        OtherdocRepository                                   $otherdocRepository,
        ConvocationRepository                                $convocationRepository,
    ): Response
    {
        $convocation = $convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $this->getUser()]);

        return $this->render('easy/viewer/viewer.html.twig', [
            'sitting' => $sitting,
            'projects' => $projectRepository->getProjectsBySitting($sitting),
            'otherDocs' => $otherdocRepository->getOtherdocsBySitting($sitting),
            'convocationName' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? "Convocation" : 'Invitation',
            'convocationFileId' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? $sitting->getConvocationFile()->getId() : $sitting->getInvitationFile()->getId(),
            'fileURL' => $this->generateUrl('file_download', ['id' => $file->getId()]),
            'selectedDocumentId' => $file->getId()
        ]);
    }

}