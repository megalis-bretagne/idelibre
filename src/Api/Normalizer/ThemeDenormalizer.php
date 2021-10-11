<?php

namespace App\Api\Normalizer;

use App\Entity\Structure;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class ThemeDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'ThemeDenormalizerCalled';

    public function __construct(private LoggerInterface $logger, private Security $security, private ThemeRepository $themeRepository)
    {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        if (!empty($context[self::ALREADY_CALLED_DENORMALIZER])) {
            return false;
        }

        return $type === Theme::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;

        /** @var Theme $theme */
        $theme = $this->denormalizer->denormalize($data, $type, $format, $context);
        $this->setParent($theme);
        $this->setFullName($theme);

        return $theme;
    }

    private function setParent(Theme $theme): void
    {
        if ($theme->getParent()) {
            return;
        }

        $root = $this->themeRepository->findRootNodeByStructure($this->getCurrentUserStructure());
        $theme->setParent($root);
    }

    private function setFullName(Theme $theme)
    {
        if ($theme->getParent()->getName() === "ROOT") {
            $theme->setFullName($theme->getName());
            return;
        }
        $theme->setFullName($theme->getParent()->getFullName() . ', ' . $theme->getName());
    }


    private function getCurrentUserStructure(): Structure
    {
        return $this->security->getUser()->getStructure();
    }
}
