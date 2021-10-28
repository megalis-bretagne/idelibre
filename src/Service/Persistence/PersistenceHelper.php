<?php

namespace App\Service\Persistence;

use App\Security\Http400Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersistenceHelper
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $em
    ) {
    }

    public function validateAndPersist(mixed $entity)
    {
        $this->validate($entity);

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function validate(mixed $entity)
    {
        $validationErrors = $this->validator->validate($entity);

        if ($validationErrors->count()) {
            $errors = [];
            /** @var ConstraintViolation $validationError */
            foreach ($validationErrors as $validationError) {
                $errors[] = "{$validationError->getMessage()} ( {$validationError->getPropertyPath()} : \"{$validationError->getInvalidValue()}\")";
            }

            throw new Http400Exception(implode(', ', $errors));
        }
    }
}
