<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChooseDeputyType;
use App\Form\SearchType;
use App\Form\UserPreferenceType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Password\ResetPassword;
use App\Security\UserLoginEntropy;
use App\Service\EventLog\EventLogManager;
use App\Service\User\PasswordInvalidator;
use App\Service\User\UserManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['user-nav'])]
#[Breadcrumb(title: 'Utilisateurs', routeName: 'user_index')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    )
    {
    }

    #[Route(path: '/user', name: 'user_index')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);
        $users = $paginator->paginate(
            $userRepository->findByStructure($this->getUser()->getStructure(), $request->query->get('search')),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['u.lastName'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search'),
            'countDeputiesAvalaible' => $this->userManager->countAvailableDeputies($this->getUser()->getStructure())
        ]);
    }

    #[Route(path: '/user/add', name: 'user_add')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(Request $request, UserManager $userManager, EventLogManager $eventLog): Response
    {
        $form = $this->createForm(UserType::class, new User(), [
            'structure' => $this->getUser()->getStructure(),
            'entropyForUser' => $this->getUser()->getStructure()->getConfiguration()->getMinimumEntropy(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $initPassword = $form->get('initPassword')->getData();

            $success = $userManager->save(
                $form->getData(),
                $initPassword ? $form->get('plainPassword')->getData() : null,
                $this->getUser()->getStructure()
            );

            if ($success) {
                $this->addFlash('success', 'Votre utilisateur a bien été ajouté');

                return $this->redirectToRoute('user_index');
            }

            $this->addFlash('error', 'Votre mot de passe n\'est pas assez fort.');
        }

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
            'suffix' => $this->getUser()->getStructure()->getSuffix(),
        ]);
    }

    #[Route(path: '/user/edit/{id}', name: 'user_edit')]
    #[IsGranted('MANAGE_USERS', subject: 'user')]
    #[Breadcrumb(title: 'Modifier {user.firstName} {user.lastName}')]
    public function edit(User $user, Request $request, UserManager $manageUser): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'structure' => $this->getUser()->getStructure(),
            'entropyForUser' => $this->getUser()->getStructure()->getConfiguration()->getMinimumEntropy(),
            'referer' => $request->headers->get('referer'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $initPassword = $form->has('initPassword') ? $form->get('initPassword')->getData() : false;

            $success = $manageUser->editUser(
                $form->getData(),
                $initPassword ? $form->get('plainPassword')->getData() : null,
            );

            if (true === $success) {
                $this->addFlash('success', 'Votre utilisateur a bien été modifié');

                return $form->get('redirect_url')->getData() ? $this->redirect($form->get('redirect_url')->getData()) : $this->redirectToRoute('user_index');
            }

            $this->addFlash('error', 'Votre mot de passe n\'est pas assez fort.');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'suffix' => $this->getUser()->getStructure()->getSuffix(),
            'user' => $user,
        ]);
    }

    #[Route(path: '/user/delete/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[IsGranted('MANAGE_USERS', subject: 'user')]
    public function delete(User $user, UserManager $manageUser, Request $request): Response
    {
        if ($this->getUser()->getid() == $user->getId()) {
            $this->addFlash('error', 'Impossible de supprimer son propre utilisateur');

            return $this->redirectToRoute('user_index');
        }
//        dd($user);
        if($user->getAssociatedWith() !== null ) {
            $this->addFlash("error", "Veuillez retirer le suppléant avant de supprimer cet utilisateur");
            return $this->redirectToRoute('user_index');

        }
        $manageUser->delete($user);
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé');

        return $this->redirectToRoute('user_index', [
            'page' => $request->get('page'),
        ]);
    }

    #[Route(path: '/user/deleteBatch', name: 'user_delete_batch')]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function deleteBatch(UserRepository $userRepository, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $userRepository->deleteActorsByStructure($this->getUser()->getStructure(), $request->request->all('users') ?? []);
            $this->addFlash('success', 'Les élus ont été supprimés.');

            return $this->redirectToRoute('user_index');
        }
        $actors = $userRepository->findActorsByStructure($this->getUser()->getStructure())->getQuery()->getResult();

        return $this->render('user/deleteBatch.html.twig', [
            'actors' => $actors,
        ]);
    }

    #[Route(path: '/user/preferences', name: 'user_preferences', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGE_PREFERENCES')]
    #[Breadcrumb(null)]

    //    #[Breadcrumb(title: 'Préférences utilisateur')]
    public function preferences(Request $request, UserManager $userManager, UserLoginEntropy $userLoginEntropy): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserPreferenceType::class, $user, [
            'entropyForUser' => $userLoginEntropy->getEntropy($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $userManager->preference(
                $form->getData(),
                $form->get('plainPassword')->getData()
            );

            if (true === $success) {
                $this->addFlash('success', 'Vos préférences utilisateur ont bien été modifiées');

                return $this->redirectToRoute('app_entrypoint');
            }
            $this->addFlash('error', 'Votre mot de passe n\'est pas assez fort.');
        }

        return $this->render('user/preferences.html.twig', [
            'form' => $form->createView(),
            'suffix' => $this->isGranted('ROLE_MANAGE_STRUCTURES') ? null : $user->getStructure()->getSuffix(),
        ]);
    }

    #[Route(path: '/user/invalidatePassword', name: 'invalidate_users_password', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function invalidateUsersPassword(PasswordInvalidator $passwordInvalidator): Response
    {
        $passwordInvalidator->invalidatePassword($this->getUser()->getStructure());
        $this->addFlash('success', 'Tous les mots de passe ont été invalidés');

        return $this->redirectToRoute('user_index');
    }

    #[Route(path: '/reload_password/{id}', name: 'user_reload_password', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGE_USERS')]
    public function reloadPassword(User $user, Request $request, ResetPassword $resetPassword): Response
    {
        if ($this->getUser()->getId() === $user->getId()) {
            $this->addFlash('error', 'Impossible de modifier son propre utilisateur');

            return $this->redirectToRoute('user_index');
        }

        $resetPassword->reloadPassword($user);

        $this->addFlash(
            'success',
            'Un e-mail de réinitialisation du mot de passe a été envoyé'
        );

        if (empty($this->getUser()->getStructure())) {
            return $this->redirectToRoute('admin_index');
        } else {
            return $this->redirectToRoute('user_index', [
                'page' => $request->get('page'),
            ]);
        }
    }




    /**  Ajoute un suppléant depuis les icone de coté du user index     */
    #[Route('/user/{id}/add/deputy/', name: 'user_add_deputy')]
    #[IsGranted('ROLE_MANAGE_USERS', subject: 'user')]
    public function addDeputy(User $user, Request $request): Response
    {
        $form = $this->createForm(ChooseDeputyType::class, $user, [
            'structure' => $this->getUser()->getStructure(),
            'toExcludes' => [$user],
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->addDeputy($form->getData());
            $this->addFlash('success', 'Le suppléant a été ajouté');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/add_deputy.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/{id}/delete/procuration-deputy/', name: 'sitting_procuration_deputy_delete')]
    #[IsGranted('ROLE_MANAGE_USERS', subject: 'user')]
    public function removeProcuration(User $user): Response
    {
        $this->userManager->removeProcurationOrDeputy($user);
        $this->addFlash('success', "L'élu associé a été retiré");

        return $this->redirectToRoute('user_index');
    }

    #[Route('/user/list/deputies', name: 'user_deputies_list', methods: ['GET'])]
    public function getDeputyList(?User $user): Response
    {
        $toExcludes = [];
        $user ? $toExcludes[] = $user : $toExcludes[] = null;
        return $this->render('include/user_lists/_available_actors.html.twig', [
            "availables" => $this->userRepository->findAvailableDeputiesInStructure($this->getUser()->getStructure(), [])->getQuery()->getResult(),
        ]);
    }

    #[Route('/user/list/actors', name: 'user_actors_list', methods: ['GET'])]
    public function getActorsList(?User $user): Response
    {
        $toExcludes = [];
        $user ? $toExcludes[] = $user : $toExcludes[] = null;
        return $this->render('include/user_lists/_available_actors.html.twig', [
            "availables" => $this->userRepository->findAvailableActorsInStructureWithNoAssociation($this->getUser()->getStructure(), [])->getQuery()->getResult(),
        ]);
    }
}
