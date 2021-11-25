<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Form\SearchType;
use App\Form\SittingType;
use App\Repository\ConvocationRepository;
use App\Repository\ProjectRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\Seance\ActorManager;
use App\Service\Seance\SittingManager;
use App\Service\Zip\ZipSittingGenerator;
use App\Sidebar\Annotation\Sidebar;
use App\Sidebar\State\SidebarState;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['sitting-nav'])]
#[Breadcrumb(title: 'Séances', routeName: 'sitting_index')]
class SittingController extends AbstractController
{
    #[Route(path: '/sitting', name: 'sitting_index')]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    public function index(PaginatorInterface $paginator, Request $request, SittingManager $sittingManager, SidebarState $sidebarState): Response
    {
        $formSearch = $this->createForm(SearchType::class);
        $sittings = $paginator->paginate(
            $sittingManager->getListSittingByStructureQuery($this->getUser(), $request->query->get('search'), $request->query->get('status')),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );
        if ($status = $request->query->get('status')) {
            $sidebarState->addActiveNavs(['sitting-nav', "sitting-${status}-nav"]);
        }

        return $this->render('sitting/index.html.twig', [
            'sittings' => $sittings,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);
    }

    #[Route(path: '/sitting/add', name: 'sitting_add')]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Ajouter')]
    public function createSitting(Request $request, SittingManager $sittingManager): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure(), 'user' => $this->getUser()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sittingId = $sittingManager->save(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $form->get('invitationFile')->getData(),
                $this->getUser()->getStructure()
            );

            return $this->redirectToRoute('edit_sitting_actor', ['id' => $sittingId]);
        }

        return $this->render('sitting/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/actors', name: 'edit_sitting_actor', methods: ['GET'])]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editUsers(Sitting $sitting, Request $request, ActorManager $actorManager, ConvocationManager $convocationManager): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_actors.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/projects', name: 'edit_sitting_project')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editProjects(Sitting $sitting): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_projects.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/edit/{id}', name: 'edit_sitting_information')]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editInformation(Sitting $sitting, Request $request, SittingManager $sittingManager): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }
        $form = $this->createForm(SittingType::class, $sitting, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sittingManager->update(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $form->get('invitationFile')->getData(),
            );

            $this->addFlash('success', 'Modifications enregistrées');

            return $this->redirectToRoute('edit_sitting_information', ['id' => $sitting->getId()]);
        }

        return $this->render('sitting/edit_information.html.twig', [
            'form' => $form->createView(),
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/cancel', name: 'edit_sitting_information_cancel')]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function editInformationCancel(Sitting $sitting): Response
    {
        $this->addFlash('success', 'Modifications annulées');

        return $this->redirectToRoute('edit_sitting_information', ['id' => $sitting->getId()]);
    }

    #[Route(path: '/sitting/delete/{id}', name: 'sitting_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function delete(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->delete($sitting);
        $this->addFlash('success', 'La séance a bien été supprimée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/show/{id}/information', name: 'sitting_show_information', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showInformation(Sitting $sitting, SittingManager $sittingManager, SidebarState $sidebarState): Response
    {
        $sidebarState->addActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_information.html.twig', [
            'isAlreadySent' => $sittingManager->isAlreadySent($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);
    }

    #[Route(path: '/sitting/show/{id}/actors', name: 'sitting_show_actors', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showActors(Sitting $sitting, ConvocationRepository $convocationRepository, SidebarState $sidebarState): Response
    {
        $sidebarState->addActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_actors.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/show/{id}/projects', name: 'sitting_show_projects', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showProjects(Sitting $sitting, ConvocationRepository $convocationRepository, ProjectRepository $projectRepository, SidebarState $sidebarState): Response
    {
        $sidebarState->addActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_projects.html.twig', [
            'sitting' => $sitting,
            'projects' => $projectRepository->getProjectsWithAssociatedEntities($sitting),
        ]);
    }

    #[Route(path: '/sitting/zip/{id}', name: 'sitting_zip', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function getZipSitting(Sitting $sitting, ZipSittingGenerator $zipSittingGenerator): Response
    {
        $zipPath = $zipSittingGenerator->getAndCreateZipPath($sitting);
        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '.zip'
        );
        $response->headers->set('X-Accel-Redirect', $zipPath);

        return $response;
    }

    #[Route(path: '/sitting/pdf/{id}', name: 'sitting_full_pdf', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function getFullPdfSitting(Sitting $sitting, PdfSittingGenerator $pdfSittingGenerator): Response
    {
        $pdfPath = $pdfSittingGenerator->getPdfPath($sitting);
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '.pdf'
        );
        $response->headers->set('X-Accel-Redirect', $pdfPath);

        return $response;
    }

    #[Route(path: '/sitting/archive/{id}', name: 'sitting_archive', methods: ['POST'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function archiveSitting(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->archive($sitting);
        $this->addFlash('success', 'La séance a été classée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/unarchive/{id}', name: 'sitting_unarchive', methods: ['POST'])]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function unArchiveSitting(Sitting $sitting, SittingManager $sittingManager, Request $request)
    {
        $sittingManager->unArchive($sitting);
        $this->addFlash('success', 'La séance a été déclassée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    private function activeSidebarNav(bool $isArchived): string
    {
        if ($isArchived) {
            return 'sitting-archived-nav';
        }

        return 'sitting-active-nav';
    }
}
