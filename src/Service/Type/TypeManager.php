<?php

namespace App\Service\Type;

use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class TypeManager
{
    private TypeRepository $typeRepository;
    private EntityManagerInterface $em;

    public function __construct(
        TypeRepository $typeRepository,
        EntityManagerInterface $em
    ) {
        $this->typeRepository = $typeRepository;
        $this->em = $em;
    }

    public function save(
        Type $type,
        iterable $associatedActors,
        iterable $associatedEmployees,
        iterable $associatedGuests,
        Structure $structure
    ): void {
        $type->setAssociatedUsers([...$associatedActors, ...$associatedEmployees, ...$associatedGuests]);
        $type->setStructure($structure);
        $this->em->persist($type);
        $this->em->flush();
    }

    public function delete(Type $type): void
    {
        $this->em->remove($type);
        $this->em->flush();
    }
}
