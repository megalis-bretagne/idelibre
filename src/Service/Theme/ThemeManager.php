<?php


namespace App\Service\Theme;


use App\Entity\Structure;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ThemeManager
{
    private ThemeRepository $themeRepository;
    private EntityManagerInterface $em;

    public function __construct(ThemeRepository $themeRepository, EntityManagerInterface $em)
    {
        $this->themeRepository = $themeRepository;
        $this->em = $em;
    }

    public function save(Theme $theme, Structure $structure, ?Theme $parentTheme = null)
    {
        $theme->setStructure($structure);
        if(!$parentTheme) {
            $parentTheme = $this->themeRepository->findRootNodeByStructure($structure);
        }

        $theme->setParent($parentTheme);
        $this->em->persist($theme);
        $this->em->flush();
    }

    public function update(Theme $theme)
    {
        $this->em->persist($theme);
        $this->em->flush();
    }

    public function delete(Theme $theme)
    {
        $this->em->remove($theme);
        $this->em->flush();
    }
}
