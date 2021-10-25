<?php

namespace App\Controller\ApiV2;

use App\Entity\Structure;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Persistence\PersistenceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * body {
 *   "name":"string",
 * }.
 */
#[Route('/api/v2/structures/{structureId}/themes')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class ThemeApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private EntityManagerInterface $em,
        private ThemeRepository $themeRepository,
        private PersistenceHelper $persistenceHelper
    ) {
    }

    #[Route('', name: 'get_all_themes', methods: ['GET'])]
    public function getAll(
        Structure $structure,
        ThemeRepository $themeRepository
    ): JsonResponse {
        $themes = $themeRepository->findChildrenFromStructure($structure)->getQuery()->getResult();

        return $this->json($themes, context: ['groups' => 'theme:read']);
    }

    #[Route('/{id}', name: 'get_theme', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'theme'])]
    public function getById(
        Structure $structure,
        Theme $theme
    ): JsonResponse {
        return $this->json($theme, context: ['groups' => ['theme:read', 'theme:detail']]);
    }

    #[Route('', name: 'add_theme', methods: ['POST'])]
    #[IsGranted('API_RELATION_THEME', subject: ['structure', 'data'])]
    public function add(Structure $structure, ThemeRepository $themeRepository, array $data): JsonResponse
    {
        $context = ['groups' => ['theme:write', 'theme:write:post'], 'normalize_relations' => true];
        /** @var Theme $theme */
        $theme = $this->denormalizer->denormalize($data, Theme::class, context: $context);

        $theme->setStructure($structure);

        $this->setParent($theme, $structure);
        $this->setFullName($theme);

        $this->persistenceHelper->validateAndPersist($theme);

        return $this->json($theme, status: 201, context: ['groups' => ['theme:read', 'theme:detail']]);
    }

    #[Route('/{id}', name: 'edit_theme', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'theme'])]
    public function update(Structure $structure, Theme $theme, array $data): JsonResponse
    {
        $context = ['object_to_populate' => $theme, 'groups' => ['theme:write']];

        /** @var Theme $updatedTheme */
        $updatedTheme = $this->denormalizer->denormalize($data, Theme::class, context: $context);

        $this->setFullName($updatedTheme);

        $this->persistenceHelper->validateAndPersist($updatedTheme);

        return $this->json($updatedTheme, context: ['groups' => ['theme:detail', 'theme:read']]);
    }

    #[Route('/{id}', name: 'delete_theme', methods: ['DELETE'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'theme'])]
    public function delete(Structure $structure, Theme $theme): JsonResponse
    {
        $this->em->remove($theme);
        $this->em->flush();

        return $this->json(null, status: 204);
    }

    private function setParent(Theme $theme, Structure $structure): void
    {
        if ($theme->getParent()) {
            return;
        }

        $root = $this->themeRepository->findRootNodeByStructure($structure);
        $theme->setParent($root);
    }

    private function setFullName(Theme $theme)
    {
        if ('ROOT' === $theme->getParent()->getName()) {
            $theme->setFullName($theme->getName());

            return;
        }
        $theme->setFullName($theme->getParent()->getFullName() . ', ' . $theme->getName());
    }
}
