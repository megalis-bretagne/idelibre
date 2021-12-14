<?php

namespace App\Controller\Api32;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class ThemeController extends AbstractController
{
    /**
     * @deprecated
     * @Route("/api/v1/themes", name="list_themes", methods={"GET"})
     */
    public function listThemes(
        Request $request,
        VerifyToken $verifyToken,
        ThemeRepository $themeRepository
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $themes = $themeRepository->findBy(['structure' => $structure]);

        $formattedThemes = [];
        foreach ($themes as $theme) {
            if ('ROOT' === $theme->getName()) {
                continue;
            }
            $formattedThemes[] = ['id' => $theme->getId(), 'name' => $theme->getName()];
        }

        return $this->json($formattedThemes);
    }

    /**
     * @deprecated
     * @Route("/api/v1/themes", name="create_themes", methods={"POST"})
     */
    public function createThemes(
        Request $request,
        VerifyToken $verifyToken,
        ThemeManager $themeManager
    ): JsonResponse {
        $structure = $verifyToken->validate($request);

        $content = json_decode($request->getContent(), true);
        $themeName = $content['name'];
        if (!$themeName) {
            throw new BadRequestException('name is required');
        }

        $theme = (new Theme())->setName($themeName);
        $savedTheme = $themeManager->save($theme, $structure);

        return $this->json(['id' => $savedTheme->getId()]);
    }
}
