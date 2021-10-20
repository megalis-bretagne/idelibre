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
    private StructureRepository $structureRepository;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;
    private ImpersonateStructure $impersonateStructure;

    public function __construct(
        StructureRepository $structureRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        ImpersonateStructure $impersonateStructure
    ) {
        $this->structureRepository = $structureRepository;
        $this->em = $em;
        $this->passwordHasher = $userPasswordHasher;
        $this->validator = $validator;
        $this->impersonateStructure = $impersonateStructure;
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
