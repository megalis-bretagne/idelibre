<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeWithParentType;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['theme-nav'])]
#[Breadcrumb(title: 'Thèmes', routeName: 'theme_index')]
class ThemeController extends AbstractController
{
    public function __construct(
        private readonly ThemeManager $themeManager,
    ) {
    }

    #[Route(path: '/theme/index', name: 'theme_index')]
    #[IsGranted('ROLE_MANAGE_THEMES')]
    public function index(ThemeRepository $themeRepository): Response
    {
        $root = $themeRepository->findOneBy(['name' => 'ROOT', 'structure' => $this->getUser()->getStructure()]);
        if (!empty($root)) {
            $themes = $themeRepository->getChildren($root, false, ['fullName']);
        }

        return $this->render('theme/index.html.twig', [
            'themes' => $themes ?? null,
        ]);
    }

    #[Route(path: '/theme/add', name: 'theme_add')]
    #[IsGranted('ROLE_MANAGE_THEMES')]
    #[Breadcrumb(title: 'Ajouter un thème')]
    public function add(Request $request): Response
    {
        $form = $this->createForm(ThemeWithParentType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->themeManager->save($form->getData(), $this->getUser()->getStructure());

            $this->addFlash('success', 'Votre thème a bien été ajouté');

            return $this->redirectToRoute('theme_index');
        }

        return $this->render('theme/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/theme/edit/{id}', name: 'theme_edit')]
    #[IsGranted('MANAGE_THEMES', subject: 'theme')]
    #[Breadcrumb(title: 'Modification du thème {theme.name}')]
    public function edit(Theme $theme, Request $request): Response
    {
        $form = $this->createForm(ThemeWithParentType::class, $theme, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->themeManager->update($form->getData());

            $this->addFlash('success', 'Votre thème a bien été modifié');

            return $this->redirectToRoute('theme_index');
        }

        return $this->render('theme/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modification du thème ' . $theme->getName(),
        ]);
    }

    #[Route(path: '/theme/delete/{id}', name: 'theme_delete', methods: ['DELETE'])]
    #[IsGranted('MANAGE_THEMES', subject: 'theme')]
    public function delete(Theme $theme): Response
    {
        $this->themeManager->delete($theme);
        $this->addFlash('success', 'Le thème a bien été supprimé');

        return $this->redirectToRoute('theme_index');
    }
}
