<?php

namespace App\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class HiddenEntityTransformer implements DataTransformerInterface
{
    public function __construct(
        private string $entityName,
        private ManagerRegistry $managerRegistry
    )
    {
    }

    public function transform(mixed $value)
    {
        if (!$value instanceof $this->entityName) {
            throw new TransformationFailedException('Value must be an instance of ' . $this->entityName);
        }

        return $value->getId();
    }

    public function reverseTransform($value)
    {
        try {
            $repository = $this->managerRegistry->getRepository($this->entityName);
            $entity = $repository->findOneBy(['id' => $value]);
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage());
        }

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->entityName, $value));
        }

        return $entity;
    }
}
