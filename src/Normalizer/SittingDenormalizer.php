<?php

namespace App\Normalizer;

use App\Entity\Sitting;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SittingDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        #[Autowire(service: ObjectNormalizer::class)]
        private readonly DenormalizerInterface $denormalizer
    )
    {
    }


    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return $type === Sitting::class;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if(isset($data["isRemoteAllowed"])) {
            $data["isRemoteAllowed"] = boolval($data["isRemoteAllowed"]);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}