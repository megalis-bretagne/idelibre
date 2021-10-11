<?php

namespace App\Api\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var User $user */
        $user = $object;

        $user->setUsername($this->removeSuffix($user->getUsername()));

        return $this->normalizer->normalize($user, $format, $context);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function removeSuffix(string $username): string
    {
        if (str_contains($username, '@')) {
            return preg_replace('/@.*/', '', $username);
        }

        return $username;
    }
}
