<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    /**
     * @Route("/theme/generate", name="theme_generate")
     */
    public function generate(ThemeRepository $repo, EntityManagerInterface $em)
    {
        $structure = $this->getUser()->getStructure();
        //$repo = $em->getRepository(Theme::class);

//$repo = $em->getRepository('Entity\Category')
        // level 0: Home
        $home = new Theme();
        $home->setName('ROOT');
        $home->setStructure($structure);

        // level 1: Bikes, Components, Wheels & Tyres
        $bikes = new Theme();
        $bikes->setName('Bikes');
        $bikes->setParent($home);
        $bikes->setStructure($structure);
        $components = new Theme();
        $components->setName('Components');
        $components->setParent($home);
        $components->setStructure($structure);
        $wheelsAndTyres = new Theme();
        $wheelsAndTyres->setName('Wheels & Tyres');
        $wheelsAndTyres->setParent($home);
        $wheelsAndTyres->setStructure($structure);

        $banquettes = new Theme();
        $banquettes->setName('Banquette');
        $banquettes->setParent($home);
        $banquettes->setStructure($structure);

        //level 2

        $vtt = new Theme();
        $vtt->setName('Vtt');
        $vtt->setParent($bikes);
        $vtt->setStructure($structure);
        $vtc = new Theme();
        $vtc->setName('Vtc');
        $vtc->setParent($bikes);
        $vtc->setStructure($structure);
        $em->persist($home);
        $em->persist($bikes);
        $em->persist($components);
        $em->persist($vtt);
        $em->persist($vtc);
        $em->persist($banquettes);

        $em->persist($wheelsAndTyres);



        $em->flush();
        //$repo->persistAsNextSiblingOf($banquettes, $vtt);

        //$em->flush();



        // demonstrate using repository functions
        $bike = $repo->findOneBy(['name' => 'ROOT']);
        //$bikes = $repo->childrenHierarchy($bike);

        $bikes = $repo->getChildren($bike);
        dd($bikes);

        return $this->render('theme/index.html.twig', [
            'controller_name' => 'ThemeController',
        ]);
    }


    /**
     * @Route("/theme/index", name="theme_index")
     */
    public function index(ThemeRepository $themeRepository)
    {
        $root = $themeRepository->findOneBy(['name' => 'ROOT', 'structure' => $this->getUser()->getStructure()]);
        $themes = $themeRepository->getChildren($root );

        return $this->render('theme/index.html.twig', [
            'themes' => $themes,
        ]);
    }


    /**
     * @Route("/theme/add", name="theme_add")
     */
    public function add(ThemeManager $themeManager, Request $request)
    {
        $form = $this->createForm(ThemeType::class, null, ["structure" => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $themeManager->save($form->getData(), $this->getUser()->getStructure(), $form->get('parentTheme')->getData());

            $this->addFlash('success', 'Votre theme a bien été ajouté');
            return $this->redirectToRoute('theme_index');
        }
        return $this->render('theme/add.html.twig', [
            'form' => $form->createView()
        ]);

    }


    /**
     * @Route("/theme/edit/{id}", name="theme_edit")
     */
    public function edit(Theme $theme)
    {

    }

    /**
     * @Route("/theme/delete/{id}", name="theme_delete", methods={"DELETE"})
     */
    public function delete(Theme $theme)
    {

    }


    /**
     * @Route("/theme/check", name="theme_delete")
     */
    public function check(ThemeRepository $themeRepository)
    {
        $themeRepository->findChildrenFromStructure($this->getUser()->getStructure());
    }


}
