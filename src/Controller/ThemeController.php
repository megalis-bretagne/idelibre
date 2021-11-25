<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;
use App\Form\ThemeWithParentType;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['theme-nav'])]
#[Breadcrumb(title: 'Thèmes', routeName: 'theme_index')]
class ThemeController extends AbstractController
{
    #[Route(path: '/theme/index', name: 'theme_index')]
    #[IsGranted(data: 'ROLE_MANAGE_THEMES')]
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
    #[IsGranted(data: 'ROLE_MANAGE_THEMES')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(ThemeManager $themeManager, Request $request): Response
    {
        $form = $this->createForm(ThemeWithParentType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $themeManager->save($form->getData(), $this->getUser()->getStructure(), $form->get('parentTheme')->getData());

            $this->addFlash('success', 'Votre thème a bien été ajouté');

            return $this->redirectToRoute('theme_index');
        }

        return $this->render('theme/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/theme/edit/{id}', name: 'theme_edit')]
    #[IsGranted(data: 'MANAGE_THEMES', subject: 'theme')]
    #[Breadcrumb(title: 'Modifier {theme.name}')]
    public function edit(Theme $theme, ThemeManager $themeManager, Request $request): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $themeManager->update($form->getData());

            $this->addFlash('success', 'Votre thème a bien été modifié');

            return $this->redirectToRoute('theme_index');
        }

        return $this->render('theme/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/theme/delete/{id}', name: 'theme_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MANAGE_THEMES', subject: 'theme')]
    public function delete(Theme $theme, ThemeManager $themeManager): Response
    {
        $themeManager->delete($theme);
        $this->addFlash('success', 'Le thème a bien été supprimé');

        return $this->redirectToRoute('theme_index');
    }
}
