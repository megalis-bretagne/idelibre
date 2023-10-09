<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupStructureType;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Service\Group\GroupManager;
use App\Service\ValidationTrait;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['platform-nav', 'group-nav'])]
#[Breadcrumb(title: 'Plateforme', routeName: 'structure_index')]
#[Breadcrumb(title: 'Groupes', routeName: 'group_index')]
class GroupController extends AbstractController
{
    use ValidationTrait;

    #[Route(path: '/group', name: 'group_index')]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function index(GroupRepository $groupRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $groupQueryAll = $groupRepository->findAllQuery();
        $groups = $paginator->paginate(
            $groupQueryAll,
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table')
        );

        return $this->render('group/index.html.twig', [
            'groups' => $groups,
        ]);
    }

    #[Route(path: '/group/add', name: 'group_add')]
    #[IsGranted('ROLE_SUPERADMIN')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(Request $request, GroupManager $groupManager, ParameterBagInterface $bag): Response
    {
        $form = $this->createForm(GroupType::class, null, [
            'isNew' => true,
            'entropyForUser' => $bag->get('minimumEntropyForUserWithRoleHigh'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $groupManager->create(
                $form->getData(),
                $form->get('user')->getData(),
            );

            if (!empty($errors)) {
                $this->addErrorToForm($form->get('user'), $errors);

                return $this->render('group/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Le groupe a bien été créé');

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/group/manage/{id}', name: 'group_manage')]
    #[IsGranted('ROLE_SUPERADMIN')]
    #[Breadcrumb(title: 'Gérer {group.name}')]
    public function manage(Group $group, Request $request, GroupManager $groupManager): Response
    {
        $form = $this->createForm(GroupStructureType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupManager->associateStructure($form->getData());
            $this->addFlash('success', 'Les structures ont bien été mises à jour');

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/manage.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/group/edit/{id}', name: 'group_edit')]
    #[IsGranted('ROLE_SUPERADMIN')]
    #[Breadcrumb(title: 'Modifier {group.name}')]
    public function edit(Group $group, Request $request, GroupManager $groupManager): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupManager->save($form->getData());
            $this->addFlash('success', 'Le groupe a bien été modifié');

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/group/delete/{id}', name: 'group_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function delete(Group $group, GroupManager $groupManager, Request $request): Response
    {
        $groupManager->delete($group);
        $this->addFlash('success', 'Le groupe a bien été supprimé');

        return $this->redirectToRoute('group_index', [
            'page' => $request->get('page'),
        ]);
    }
}
