<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Get related entity by id
 * add ['normalize_relations' => true ] to context.
 */
class EntityRelationDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $entity = $this->getEntityById($type, $data);
        if (!$entity) {
            throw new EntityDenormalizerException("entity $type with id $data not found");
        }

        return $this->getEntityById($type, $data);
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        if (!isset($context['normalize_relations']) || !$context['normalize_relations']) {
            return false;
        }

        return $this->isUUID($data);
    }

    private function isUUID($uuid): bool
    {
        return is_string($uuid) && (1 === preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid));
    }

    public function getEntityById($entityClass, string $id): mixed
    {
        $repository = $this->entityManager->getRepository($entityClass);

        return $repository->find($id);
    }
}
