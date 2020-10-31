<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Form\AddActorType;
use App\Form\SearchType;
use App\Form\SittingType;
use App\Repository\ConvocationRepository;
use App\Repository\ProjectRepository;
use App\Repository\SittingRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\Seance\ActorManager;
use App\Service\Seance\SittingManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Seance", routeName="sitting_index")
 *
 */
class SittingController extends AbstractController
{
    /**
     * @Route("/sitting", name="sitting_index")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function index(SittingRepository $sittingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);

        $sittings = $paginator->paginate(
            $sittingRepository->findByStructure($this->getUser()->getStructure(), $request->query->get('search')),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('sitting/index.html.twig', [
            'sittings' => $sittings,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName()
        ]);
    }

    /**
     * @Route("/sitting/add", name="sitting_add")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Ajouter")
     */
    public function addInformation(Request $request, SittingManager $sittingManager): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sittingManager->save(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $this->getUser()->getStructure()
            );

            $this->addFlash('success', 'votre séance a bien été ajoutée');
            return $this->redirectToRoute('sitting_index');
        }
        return $this->render('sitting/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/sitting/{id}/actors", name="edit_sitting_actor")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Gérer les acteurs")
     */
    public function editUsers(Sitting $sitting, Request $request, ActorManager $actorManager, ConvocationManager $convocationManager): Response
    {
        $form = $this->createForm(AddActorType::class, null, ['structure' => $sitting->getStructure(), 'sitting' => $sitting]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $convocationManager->addConvocations($form->get('notAssociatedActors')->getData(), $sitting);
            $this->addFlash('success', 'les utilisateurs ont été modifiés');
            return $this->redirectToRoute('sitting_index');
        }

        return $this->render('sitting/actors.html.twig', [
            'convocatedActors' => $actorManager->getActorsBySitting($sitting),
            'form' => $form->createView(),
            'sitting' => $sitting
        ]);
    }

    /**
     * @Route("/sitting/{id}/projects", name="edit_sitting_project")
     * IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Gérer les projets")
     */
    public function editProjects(Sitting $sitting)
    {
        return $this->render('sitting/projects.html.twig', [
            'sitting' => $sitting
        ]);
    }


    /**
     * @Route("/sitting/edit/{id}", name="edit_sitting_information")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     * @Breadcrumb("Modifier")
     */
    public function editInformation(Sitting $sitting, Request $request, SittingManager $sittingManager)
    {
        $form = $this->createForm(SittingType::class, $sitting, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sittingManager->update(
                $form->getData(),
                $form->get('convocationFile')->getData()
            );

            $this->addFlash('success', 'votre séance a bien été modifiée');
            return $this->redirectToRoute('sitting_index');
        }
        return $this->render('sitting/information.html.twig', [
            'form' => $form->createView(),
            'sitting' => $sitting
        ]);
    }


    /**
     * @Route("/sitting/delete/{id}", name="sitting_delete", methods={"DELETE"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function delete(Sitting $sitting, SittingManager $sittingManager, Request $request)
    {
        $sittingManager->delete($sitting);
        $this->addFlash('success', 'la séance a bien été supprimée');
        return $this->redirectToRoute('sitting_index', [
            'page' => $request->get('page')
        ]);
    }

    /**
     * @Route("/sitting/show/{id}", name="sitting_show", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function show(Sitting $sitting, ConvocationRepository $convocationRepository, ProjectRepository $projectRepository)
    {
        return $this->render('sitting/details.html.twig', [
            'sitting' => $sitting,
            'convocations' => $convocationRepository->getConvocationsBySitting($sitting),
            'projects' => $projectRepository->getProjectsWithAssociatedEntities($sitting),
            'timezone' => $sitting->getStructure()->getTimezone()->getName()
        ]);
    }
}
