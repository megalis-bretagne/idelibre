<?php

namespace App\Service\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private StructureRepository $structureRepository,
        private RoleManager $roleManager
    )
    {
    }

    public function save(Group $group): void
    {
        $this->em->persist($group);
        $this->em->flush();
    }

    public function associateStructure(Group $group): void
    {
        $structures = $this->structureRepository->findBy(['group' => $group]);

        foreach ($structures as $structure) {
            $structure->setGroup(null);
        }

        foreach ($group->getStructures() as $structure) {
            $structure->setGroup($group);
        }

        $this->em->persist($group);
        $this->em->flush();
    }

    public function create(Group $group, User $user, string $plainPassword): ?ConstraintViolationListInterface
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $errors = $this->validator->validate($user);

        if (count($errors)) {
            return $errors;
        }

        $user->setRole($this->roleManager->getGroupAdminRole());
        $this->em->persist($user);
        $group->addUser($user);

        $this->em->persist($group);
        $this->em->flush();

        return null;
    }

    public function delete(Group $group): void
    {
        $this->em->remove($group);
        $this->em->flush();
    }
}
