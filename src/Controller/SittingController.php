<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Form\AddActorType;
use App\Form\SearchType;
use App\Form\SittingType;
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
     * @Route("/sitting/index", name="sitting_index")
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
    public function add(Request $request, SittingManager $seanceManager): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $seanceManager->save(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $this->getUser()->getStructure()
            );

            $this->addFlash('success', 'votre séance a bien été ajouté');
            return $this->redirectToRoute('sitting_index');
        }
        return $this->render('sitting/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/sitting/{id}/actors", name="sitting_actor")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function editUsers(Sitting $sitting, Request $request, ActorManager $actorManager, ConvocationManager $convocationManager): Response
    {
        $form = $this->createForm(AddActorType::class, null, ['structure' => $sitting->getStructure(), 'sitting' => $sitting]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $convocationManager->addConvocations($form->get('notAssociatedActors')->getData(), $sitting);
            $this->addFlash('success', 'les utilisateurs ont été modifié');
            return $this->redirectToRoute('sitting_index');
        }

        return $this->render('sitting/actors.html.twig', [
            'convocatedActors' => $actorManager->getActorsBySitting($sitting),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/sitting/{id}/projects", name="sitting_project")
     * IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function editProjects(Sitting $sitting){

        // todo get theme lists

        return $this->render('sitting/projects.html.twig', [
        ]);
    }


    /**
     * @Route("/sitting/edit/{id}", name="sitting_edit")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function edit()
    {
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
}
