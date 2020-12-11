<?php

namespace App\Controller\api;

use App\Service\Theme\ThemeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    /**
     * @Route("/api/themes", name="api_theme_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function getThemes(ThemeManager $themeManager): JsonResponse
    {
        return $this->json(
            $themeManager->getThemesFromStructure($this->getUser()->getStructure()),
            200,
            [],
            ['groups' => ['theme']]
        );
    }
}
