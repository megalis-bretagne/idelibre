<?php

namespace App\Service\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Security\Password\ResetPassword;
use App\Service\role\RoleManager;
use App\Service\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly StructureRepository $structureRepository,
        private readonly RoleManager $roleManager,
        private readonly UserManager $userManager,
        private readonly ResetPassword $resetPassword,
    ) {
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

    public function create(Group $group, User $user): ?ConstraintViolationListInterface
    {
        $user = $this->userManager->setFirstPassword($user);
        $errors = $this->validator->validate($user);

        if (count($errors)) {
            return $errors;
        }

        $user->setRole($this->roleManager->getGroupAdminRole());
        $this->em->persist($user);
        $group->addUser($user);

        $this->em->persist($group);
        $this->em->flush();

        $this->resetPassword->sendEmailDefinePassword($user);

        return null;
    }

    public function delete(Group $group): void
    {
        $this->em->remove($group);
        $this->em->flush();
    }
}
