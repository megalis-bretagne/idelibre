<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Form\SearchType;
use App\Form\StructureInformationType;
use App\Form\StructureType;
use App\Repository\StructureRepository;
use App\Service\RoleTrait;
use App\Service\Structure\StructureCreator;
use App\Service\Structure\StructureManager;
use App\Service\ValidationTrait;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Structures", routeName="structure_index")
 * @Sidebar(active={"platform-nav","structure-nav"})
 */
class StructureController extends AbstractController
{
    use ValidationTrait;
    use RoleTrait;

    #[Route(path: '/structure', name: 'structure_index')]
    #[IsGranted(data: 'ROLE_MANAGE_STRUCTURES')]
    public function index(StructureRepository $structureRepository, PaginatorInterface $paginator, Request $request) : Response
    {
        $formSearch = $this->createForm(SearchType::class);
        if ($this->isSuperAdmin($this->getUser())) {
            $structureQueryList = $structureRepository->findAllQueryBuilder($request->query->get('search'));
        } else {
            $structureQueryList = $structureRepository->findByGroupQueryBuilder($this->getUser()->getGroup(), $request->query->get('search'));
        }
        $structures = $paginator->paginate(
            $structureQueryList,
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['s.name'],
                'defaultSortDirection' => 'asc',
            ]
        );
        return $this->render('structure/index.html.twig', [
            'structures' => $structures,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'isStructureCreator' => $this->isSuperAdmin($this->getUser()) || $this->getUser()->getGroup()->getIsStructureCreator(),
        ]);
    }

    /**
     * @Breadcrumb("Ajouter")
     */
    #[Route(path: '/structure/add', name: 'structure_add')]
    #[IsGranted(data: 'CREATE_STRUCTURE')]
    public function add(Request $request, StructureCreator $structureCreator) : Response
    {
        $form = $this->createForm(StructureType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $structureCreator->create(
                $form->getData(),
                $form->get('user')->getData(),
                $form->get('user')->get('plainPassword')->getData(),
                $this->getUser()->getGroup()
            );

            if (!empty($errors)) {
                $this->addErrorToForm($form->get('user'), $errors);

                return $this->render('structure/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $this->addFlash('success', 'La structure a été créée');

            return $this->redirectToRoute('structure_index');
        }
        return $this->render('structure/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Breadcrumb("Modifier {structure.name}")
     */
    #[Route(path: '/structure/edit/{id}', name: 'structure_edit')]
    #[IsGranted(data: 'MY_GROUP', subject: 'structure')]
    public function edit(Structure $structure, Request $request, StructureManager $structureManager) : Response
    {
        $form = $this->createForm(StructureType::class, $structure);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $structureManager->save($form->getData());
            $this->addFlash('success', 'La structure a été modifiée');

            return $this->redirectToRoute('structure_index');
        }
        return $this->render('structure/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/structure/delete/{id}', name: 'structure_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MY_GROUP', subject: 'structure')]
    public function delete(Structure $structure, StructureManager $structureManager, Request $request) : Response
    {
        $structureManager->delete($structure);
        $this->addFlash('success', 'La structure a bien été supprimée');
        return $this->redirectToRoute('structure_index', [
            'page' => $request->get('page'),
        ]);
    }

    /**
     * @Breadcrumb("Préférences")
     * @Sidebar(reset=true, active={"structure-preference-nav"})
     */
    #[Route(path: '/structure/preferences', name: 'structure_preferences')]
    #[IsGranted(data: 'ROLE_STRUCTURE_ADMIN')]
    public function preferences(Request $request, StructureManager $structureManager) : Response
    {
        $form = $this->createForm(StructureInformationType::class, $this->getUser()->getStructure());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $structureManager->save($form->getData());
            $this->addFlash('success', 'Les informations de la structure ont été mises à jour');

            return $this->redirectToRoute('app_entrypoint');
        }
        return $this->render('structure/preferences.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
