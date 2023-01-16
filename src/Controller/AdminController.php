<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchType;
use App\Form\SuperUserType;
use App\Repository\UserRepository;
use App\Service\role\RoleManager;
use App\Service\User\UserManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['platform-nav', 'admin-nav'])]
#[Breadcrumb(title: 'Administrateurs', routeName: 'admin_index')]
class AdminController extends AbstractController
{
    #[Route(path: '/admin', name: 'admin_index')]
    #[IsGranted(data: 'ROLE_MANAGE_STRUCTURES')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);
        $admins = $paginator->paginate(
            $userRepository->findSuperAdminAndGroupAdmin(
                $this->getUser()->getGroup(),
                $request->query->get('search')
            ),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['u.lastName'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('admin/index.html.twig', [
            'users' => $admins,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
        ]);
    }

    #[Route(path: '/admin/add', name: 'admin_add')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(Request $request, UserManager $userManager, RoleManager $roleManager, ParameterBagInterface $bag): Response
    {
        $form = $this->createForm(SuperUserType::class, null, [
            'entropyForUser' => $bag->get('minimumEntropyForUserWithRoleHigh'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->saveAdmin(
                $form->getData(),
                $roleManager->getSuperAdminRole(),
                null,
                true
            );

            $this->addFlash('success', 'Votre administrateur a bien été ajouté');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/admin/group/add', name: 'admin_goup_add')]
    #[IsGranted(data: 'ROLE_MANAGE_STRUCTURES')]
    #[Breadcrumb(title: 'Ajouter un administrateur de groupe')]
    public function addGroupAdmin(Request $request, UserManager $userManager, RoleManager $roleManager, ParameterBagInterface $bag): Response
    {
        $isGroupChoice = in_array('ROLE_SUPERADMIN', $this->getUser()->getRoles());

        $form = $this->createForm(SuperUserType::class, null, [
            'isGroupChoice' => $isGroupChoice,
            'entropyForUser' => $bag->get('minimumEntropyForUserWithRoleHigh'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isGroupChoice) {
                $group = $this->getUser()->getGroup();
            }
            $userManager->saveAdmin(
                $form->getData(),
                $roleManager->getGroupAdminRole(),
                $group ?? null,
                true
            );

            $this->addFlash('success', 'Votre administrateur a bien été ajouté');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/admin/edit/{id}', name: 'admin_edit')]
    #[IsGranted(data: 'MY_GROUP', subject: 'user')]
    #[Breadcrumb(title: 'Modifier {user.firstName} {user.lastName}')]
    public function edit(User $user, Request $request, UserManager $userManager, RoleManager $roleManager, ParameterBagInterface $bag): Response
    {
        $isAdminGroup = in_array('ROLE_GROUP_ADMIN', $user->getRoles());

        $form = $this->createForm(SuperUserType::class, $user, [
            'isEditMode' => true,
            'entropyForUser' => $bag->get('minimumEntropyForUserWithRoleHigh'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $roleManager->getSuperAdminRole();
            if ($isAdminGroup) {
                $group = $user->getGroup();
                $role = $roleManager->getGroupAdminRole();
            }

            $userManager->saveAdmin(
                $form->getData(),
                $role,
                $group ?? null
            );
            $userManager->saveAdmin(
                $form->getData(),
            );

            $this->addFlash('success', 'Votre administrateur a bien été modifié');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(path: '/admin/delete/{id}', name: 'admin_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MY_GROUP', subject: 'user')]
    public function delete(User $user, UserManager $userManager, Request $request): Response
    {
        if ($this->getUser()->getid() == $user->getId()) {
            $this->addFlash('error', 'Impossible de supprimer son propre utilisateur');

            return $this->redirectToRoute('admin_index');
        }
        $userManager->delete($user);
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé');

        return $this->redirectToRoute('admin_index', [
            'page' => $request->get('page'),
        ]);
    }
}
