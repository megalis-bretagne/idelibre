<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchType;
use App\Form\SuperUserType;
use App\Repository\TypeRepository;
use App\Service\role\RoleManager;
use App\Service\User\UserManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypeController extends AbstractController
{
    /**
     * @Route("/type", name="type_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_TYPES")
     *
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
            'searchTerm' => $request->query->get('search') ?? ''
        ]);
    }



    /**
     * @Route("/type/add", name="type_add")
     * @IsGranted("ROLE_MANAGE_TYPES")
     */
    public function add(Request $request, UserManager $userManager, RoleManager $roleManager): Response
    {
        $form = $this->createForm(SuperUserType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->saveAdmin(
                $form->getData(),
                $form->get('plainPassword')->getData(),
                $roleManager->getSuperAdminRole()
            );

            $this->addFlash('success', 'votre administrateur a bien été ajouté');
            return $this->redirectToRoute('admin_index');
        }
        return $this->render('admin/add.html.twig', [
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/tpye/edit/{id}", name="type_edit")
     * @IsGranted("MY_GROUP", subject="user")
     */
    public function edit(User $user, Request $request, UserManager $userManager): Response
    {
        $form = $this->createForm(SuperUserType::class, $user, ['isEditMode' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->saveAdmin(
                $form->getData(),
                $form->get('plainPassword')->getData()
            );

            $this->addFlash('success', 'votre administrateur a bien été modifié');
            return $this->redirectToRoute('admin_index');
        }
        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/type/delete/{id}", name="type_delete", methods={"DELETE"})
     * @IsGranted("MY_GROUP", subject="user")
     */
    public function delete(User $user, UserManager $userManager, Request $request): Response
    {
        if ($this->getUser()->getid() == $user->getId()) {
            $this->addFlash('error', 'Impossible de supprimer son propre utilisateur');
            return $this->redirectToRoute('admin_index');
        }
        $userManager->delete($user);
        $this->addFlash('success', 'l\'utilisateur a bien été supprimé');
        return $this->redirectToRoute('admin_index', [
            'page' => $request->get('page')
        ]);
    }
}
