<?php

namespace App\Controller\Easy;

use App\Entity\Annex;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EasyViewerController extends AbstractController
{

    #[Route(path: '/easy/viewer/project/{id}', name: 'easy_project_viewer')]
    #[IsGranted('ROLE_ACTOR')] // TODO check permission
    public function viewProject(Project $project): Response
    {

        dd('annex viewer');
    }


    #[Route(path: '/easy/viewer/annex/{id}', name: 'easy_annex_viewer')]
    #[IsGranted('ROLE_ACTOR')] // TODO check permission
    public function viewAnnex(Annex $annex): Response
    {

        dd('annex viewer');
    }


}