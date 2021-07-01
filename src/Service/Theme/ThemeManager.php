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

    public function save(Theme $theme, Structure $structure, ?Theme $parentTheme = null): Theme
    {
        $theme->setStructure($structure);

        $parent = $parentTheme ?? $this->themeRepository->findRootNodeByStructure($structure);

        $theme->setParent($parent);
        $this->em->persist($theme);
        $this->em->flush();

        $this->addFullNameToTheme($theme, $this->generateFullName($theme));

        return $theme;
    }

    public function update(Theme $theme): void
    {
        $this->em->persist($theme);
        $this->em->flush();
    }

    public function delete(Theme $theme): void
    {
        $this->em->remove($theme);
        $this->em->flush();
    }

    public function createStructureRootNode(Structure $structure): void
    {
        $rootTheme = new Theme();
        $rootTheme->setName('ROOT')
            ->setStructure($structure);

        $this->em->persist($rootTheme);
        $this->em->flush();
    }

    private function generateFullName(Theme $theme): string
    {
        $fullPath = $this->themeRepository->getPath($theme);
        $pathWithoutRoot = $this->removeRootFromArray($fullPath);
        $fullName = '';
        foreach ($pathWithoutRoot as $key => $theme) {
            $fullName .= $theme->getName();
            if ($key !== array_key_last($pathWithoutRoot)) {
                $fullName .= ', ';
            }
        }

        return $fullName;
    }

    private function removeRootFromArray(array $path): array
    {
        array_splice($path, 0, 1);

        return $path;
    }

    private function addFullNameToTheme(Theme $theme, string $fullName)
    {
        $theme->setFullName($fullName);
        $this->em->persist($theme);
        $this->em->flush();
    }

    /**
     * @return Theme[]
     */
    public function getThemesFromStructure(Structure $structure): array
    {
        $rootTheme = $this->themeRepository->findOneBy(['name' => 'ROOT', 'structure' => $structure]);

        if (!empty($rootTheme)) {
            $themes = $this->themeRepository->getChildren($rootTheme, false, ['fullName']);
        }

        return $themes ?? [];
    }

    public function createThemesFromString(string $comaSeparatedThemes, Structure $structure): Theme
    {
        $themeNames = explode(',', $comaSeparatedThemes);
        $parentTheme = null;
        foreach ($themeNames as $position => $themeName) {
            $parentTheme = $this->findOrCreateTheme(trim($themeName), $position + 1, $parentTheme, $structure);
        }

        return $parentTheme;
    }

    private function findOrCreateTheme(string $themeName, int $level, ?Theme $parentTheme, Structure $structure): Theme
    {
        $theme = $this->themeRepository->findOneBy(['structure' => $structure, 'name' => $themeName, 'lvl' => $level]);
        if ($theme) {
            return $theme;
        }
        $newTheme = (new Theme())->setName($themeName);

        return $this->save($newTheme, $structure, $parentTheme);
    }
}
