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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Seance", routeName="sitting_index")
 * @Sidebar(active={"sitting-nav"})
 */
class SittingController extends AbstractController
{
    /**
     * @Route("/sitting", name="sitting_index")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
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

    /**
     * @Route("/sitting/add", name="sitting_add")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Ajouter")
     */
    public function createSitting(Request $request, SittingManager $sittingManager): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure()]);
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

    /**
     * @Route("/sitting/edit/{id}/actors", name="edit_sitting_actor", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Modifier")
     */
    public function editUsers(Sitting $sitting, Request $request, ActorManager $actorManager, ConvocationManager $convocationManager): Response
    {
        return $this->render('sitting/edit_actors.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    /**
     * @Route("/sitting/edit/{id}/projects", name="edit_sitting_project")
     * IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Modifier")
     */
    public function editProjects(Sitting $sitting): Response
    {
        return $this->render('sitting/edit_projects.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    /**
     * @Route("/sitting/edit/{id}", name="edit_sitting_information")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Modifier")
     */
    public function editInformation(Sitting $sitting, Request $request, SittingManager $sittingManager): Response
    {
        $form = $this->createForm(SittingType::class, $sitting, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sittingManager->update(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $form->get('invitationFile')->getData(),
            );
            $this->addFlash('success', 'votre séance a bien été modifiée');

            return $this->redirectToRoute('edit_sitting_information', ['id' => $sitting->getId()]);
        }

        return $this->render('sitting/edit_information.html.twig', [
            'form' => $form->createView(),
            'sitting' => $sitting,
        ]);
    }

    /**
     * @Route("/sitting/delete/{id}", name="sitting_delete", methods={"DELETE"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function delete(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->delete($sitting);
        $this->addFlash('success', 'la séance a bien été supprimée');

        return $this->redirectToRoute('sitting_index', [
            'page' => $request->get('page'),
        ]);
    }

    /**
     * @Route("/sitting/show/{id}/information", name="sitting_show_information", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     * @Breadcrumb("Détail {sitting.name}")
     */
    public function showInformation(Sitting $sitting, SittingManager $sittingManager): Response
    {
        return $this->render('sitting/details_information.html.twig', [
            'isAlreadySent' => $sittingManager->isAlreadySent($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);
    }

    /**
     * @Route("/sitting/show/{id}/actors", name="sitting_show_actors", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     * @Breadcrumb("Détail {sitting.name}")
     */
    public function showActors(Sitting $sitting, ConvocationRepository $convocationRepository): Response
    {
        return $this->render('sitting/details_actors.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    /**
     * @Route("/sitting/show/{id}/projects", name="sitting_show_projects", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     * @Breadcrumb("Détail {sitting.name}")
     */
    public function showProjects(Sitting $sitting, ConvocationRepository $convocationRepository, ProjectRepository $projectRepository): Response
    {
        return $this->render('sitting/details_projects.html.twig', [
            'sitting' => $sitting,
            'projects' => $projectRepository->getProjectsWithAssociatedEntities($sitting),
        ]);
    }

    /**
     * @Route("/sitting/zip/{id}", name="sitting_zip", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getZipSitting(Sitting $sitting, ZipSittingGenerator $zipSittingGenerator): Response
    {
        $response = new BinaryFileResponse($zipSittingGenerator->getAndCreateZipPath($sitting));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '.zip'
        );

        return $response;
    }

    /**
     * @Route("/sitting/pdf/{id}", name="sitting_full_pdf", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getFullPdfSitting(Sitting $sitting, PdfSittingGenerator $pdfSittingGenerator): Response
    {
        $response = new BinaryFileResponse($pdfSittingGenerator->getPdfPath($sitting));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '.pdf'
        );

        return $response;
    }

    /**
     * @Route("/sitting/archive/{id}", name="sitting_archive", methods={"POST"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function archiveSitting(Sitting $sitting, SittingManager $sittingManager): Response
    {
        $sittingManager->archive($sitting);
        $this->addFlash('success', 'La séance a été classée');

        return $this->redirectToRoute('sitting_index');
    }
}
