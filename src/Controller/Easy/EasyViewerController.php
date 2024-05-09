<?php

namespace App\Controller\Easy;

use App\Entity\Annex;
use App\Entity\Convocation;
use App\Entity\File;
use App\Entity\Project;
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

//    #[Route(path: '/easy/viewer/project/{id}', name: 'easy_project_viewer')]
//    #[IsGranted('ROLE_ACTOR')] // TODO check permission
//    public function viewProject(
//        Project $project,
//        ProjectRepository $projectRepository,
//        OtherdocRepository $otherdocRepository,
//        ConvocationRepository $convocationRepository,
//
//    ): Response
//    {
//        $sitting = $project->getSitting();
//        $convocation = $convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $this->getUser()]);
//
//
//        return $this->render('easy/viewer/project.html.twig', [
//            'project' => $project,
//            'sitting'=> $sitting,
//            'projects' => $projectRepository->getProjectsBySitting($sitting),
//            'otherDocs' => $otherdocRepository->getOtherdocsBySitting($sitting),
//            'convocationName' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? "Convocation" : 'Invitation',
//            'convocationFileId' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? $sitting->getConvocationFile()->getId() : $sitting->getInvitationFile()->getId(),
//            'fileURL' => $this->generateUrl('file_download', ['id' => $project->getFile()->getId()])
//
//        ]);
//    }

    #[Route(path: '/easy/viewer/sitting/{sittingId}/file/{fileId}', name: 'easy_viewer')]
    #[IsGranted('ROLE_ACTOR')] // TODO check permission
    public function viewDocument(
        #[MapEntity(mapping: ['sittingId' => 'id'])] Sitting $sitting,
        #[MapEntity(mapping: ['fileId' => 'id'])] File $file,
        ProjectRepository $projectRepository,
        OtherdocRepository $otherdocRepository,
        ConvocationRepository $convocationRepository,

    ): Response
    {
        $convocation = $convocationRepository->findOneBy(['sitting' => $sitting, 'user' => $this->getUser()]);

        return $this->render('easy/viewer/viewer.html.twig', [
            'sitting'=> $sitting,
            'projects' => $projectRepository->getProjectsBySitting($sitting),
            'otherDocs' => $otherdocRepository->getOtherdocsBySitting($sitting),
            'convocationName' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? "Convocation" : 'Invitation',
            'convocationFileId' => $convocation->getCategory() === Convocation::CATEGORY_CONVOCATION ? $sitting->getConvocationFile()->getId() : $sitting->getInvitationFile()->getId(),
            'fileURL' => $this->generateUrl('file_download', ['id' => $file->getId()])

        ]);
    }


    #[Route(path: '/easy/check', name: 'easy_project_viewer_check')]
    public function check(): Response
    {
        return $this->render('easy/viewer/project.html.twig', [
        ]);


    }


    #[Route(path: '/easy/viewer/annex/{id}', name: 'easy_annex_viewer')]
    #[IsGranted('ROLE_ACTOR')] // TODO check permission
    public function viewAnnex(Annex $annex): Response
    {

        dd('annex viewer');
    }


}