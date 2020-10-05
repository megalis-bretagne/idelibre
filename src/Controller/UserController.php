<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPreferenceType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\User\UserManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_index")
     * @IsGranted("ROLE_MANAGE_USERS")
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $paginator->paginate(
            $userRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * @Route("/user/add", name="user_add")
     * @IsGranted("ROLE_MANAGE_USERS")
     */
    public function add(Request $request, UserManager $manageUser): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manageUser->save(
                $form->getData(),
                $form->get('plainPassword')->getData(),
                $this->getUser()->getStructure()
            );

            $this->addFlash('success', 'votre utilisateur a bien été ajouté');
            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/edit/{id}", name="user_edit")
     * @IsGranted("MANAGE_USERS", subject="user")
     */
    public function edit(User $user, Request $request, UserManager $manageUser): Response
    {
        $form = $this->createForm(UserType::class, $user, ['isEditMode' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manageUser->save(
                $form->getData(),
                $form->get('plainPassword')->getData(),
                $this->getUser()->getStructure()
            );

            $this->addFlash('success', 'votre utilisateur a bien été modifié');
            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete", methods={"DELETE"})
     * @IsGranted("MANAGE_USERS", subject="user")
     */
    public function delete(User $user, UserManager $manageUser, Request $request): Response
    {
        if ($this->getUser()->getid() == $user->getId()) {
            $this->addFlash('error', 'Impossible de supprimer son propre utilisateur');
            return $this->redirectToRoute('user_index');
        }
        $manageUser->delete($user);
        $this->addFlash('success', 'l\'utilisateur a bien été supprimé');
        return $this->redirectToRoute('user_index', [
            'page' => $request->get('page')
        ]);
    }


    /**
     * @Route("/user/preferences", name="user_preferences")
     * @IsGranted("ROLE_MANAGE_PREFERENCES")
     */
    public function preferences(Request $request, UserManager $userManager): Response
    {
        $form = $this->createForm(UserPreferenceType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save(
                $form->getData(),
                $form->get('plainPassword')->getData(),
                $this->getUser()->getStructure()
            );

            $this->addFlash('success', 'Vos préférences utilisateur ont bien été modifiées');
            return $this->redirectToRoute('app_entrypoint');
        }

        return $this->render('user/preferences.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
