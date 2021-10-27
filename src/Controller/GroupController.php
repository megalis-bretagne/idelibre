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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Groupes", routeName="group_index")
 */
#[Sidebar(active: ['platform-nav', 'group-nav'])]
class GroupController extends AbstractController
{
    use ValidationTrait;

    #[Route(path: '/group', name: 'group_index')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function index(GroupRepository $groupRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $groupQueryAll = $groupRepository->findAllQuery();
        $groups = $paginator->paginate(
            $groupQueryAll,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('group/index.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * @Breadcrumb("Ajouter")
     */
    #[Route(path: '/group/add', name: 'group_add')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function add(Request $request, GroupManager $groupManager): Response
    {
        $form = $this->createForm(GroupType::class, null, ['isNew' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $groupManager->create(
                $form->getData(),
                $form->get('user')->getData(),
                $form->get('user')->get('plainPassword')->getData()
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

    /**
     * @Breadcrumb("Gérer {group.name}")
     */
    #[Route(path: '/group/manage/{id}', name: 'group_manage')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
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

    /**
     * @Breadcrumb("Modifier {group.name}")
     */
    #[Route(path: '/group/edit/{id}', name: 'group_edit')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
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
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function delete(Group $group, GroupManager $groupManager, Request $request): Response
    {
        $groupManager->delete($group);
        $this->addFlash('success', 'Le groupe a bien été supprimé');

        return $this->redirectToRoute('group_index', [
            'page' => $request->get('page'),
        ]);
    }
}
