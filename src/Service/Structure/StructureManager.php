<?php

namespace App\Service\Structure;

use App\Entity\Structure;
use App\Repository\StructureRepository;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StructureManager
{
    public function __construct(
        private StructureRepository $structureRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private ImpersonateStructure $impersonateStructure
    )
    {
    }

    public function save(Structure $structure): void
    {
        $this->em->persist($structure);
        $this->em->flush();
    }

    public function delete(Structure $structure): void
    {
        $this->impersonateStructure->logoutEverySuperAdmin($structure);
        $this->em->remove($structure);
        $this->em->flush();
    }

    public function replaceReplyTo(Structure $structure, ?string $replyTo): void
    {
        $structure->setReplyTo($replyTo);
        $this->em->persist($structure);
        $this->em->flush();
    }
}
