<?php

namespace App\Api\Normalizer;

use App\Entity\Structure;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class AddStructureDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'AddStructureDenormalizerCalled';

    public function __construct(private LoggerInterface $logger, private Security $security)
    {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        if (!empty($context[self::ALREADY_CALLED_DENORMALIZER])) {
            return false;
        }

        if (!(isset($context['collection_operation_name'])) || 'post' !== $context['collection_operation_name']) {
            return false;
        }

        try {
            return (new ReflectionClass($type))->hasMethod('setStructure');
        } catch (\ReflectionException $e) {
            $this->logger->error($e);

            return false;
        }
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;

        $structurableObject = $this->denormalizer->denormalize($data, $type, $format, $context);

        $structurableObject->setStructure($this->getCurrentUserStructure());

        return $structurableObject;
    }

    private function getCurrentUserStructure(): Structure
    {
        return $this->security->getUser()->getStructure();
    }
}
