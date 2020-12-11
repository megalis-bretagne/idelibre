<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\SearchType;
use App\Form\TypeType;
use App\Repository\TypeRepository;
use App\Service\Type\TypeManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Types de séance", routeName="type_index")
 */
class TypeController extends AbstractController
{
    /**
     * @Route("/type", name="type_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_TYPES")
     */
    public function index(TypeRepository $typeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);

        $types = $paginator->paginate(
            $typeRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['t.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('type/index.html.twig', [
            'types' => $types,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
        ]);
    }

    /**
     * @Route("/type/add", name="type_add")
     * @IsGranted("ROLE_MANAGE_TYPES")
     * @Breadcrumb("Ajouter")
     */
    public function add(Request $request, TypeManager $typeManager): Response
    {
        $form = $this->createForm(TypeType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $typeManager->save($form->getData(), $this->getUser()->getStructure());

            $this->addFlash('success', 'Votre type a bien été ajouté');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/type/edit/{id}", name="type_edit")
     * @IsGranted("MANAGE_TYPES", subject="type")
     * @Breadcrumb("Modifier {type.name}")
     */
    public function edit(Type $type, Request $request, TypeManager $typeManager): Response
    {
        $form = $this->createForm(TypeType::class, $type, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $typeManager->save($form->getData(), $this->getUser()->getStructure());

            $this->addFlash('success', 'Votre type a bien été modifié');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $type,
        ]);
    }

    /**
     * @Route("/type/delete/{id}", name="type_delete", methods={"DELETE"})
     * @IsGranted("MANAGE_TYPES", subject="type")
     */
    public function delete(Type $type, TypeManager $typeManager, Request $request): Response
    {
        $typeManager->delete($type);
        $this->addFlash('success', 'Le type a bien été supprimé');

        return $this->redirectToRoute('type_index', [
            'page' => $request->get('page'),
        ]);
    }
}
