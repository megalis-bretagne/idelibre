<?php

namespace App\Service\User;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface          $validator,
        private RoleManager                 $roleManager,
        private UserRepository              $userRepository
    )
    {
    }

    public function save(User $user, ?string $plainPassword, ?Structure $structure): void
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $user->setStructure($structure);

        $this->em->persist($user);

        $this->em->flush();
    }

    public function saveStructureAdmin(User $user, ?string $plainPassword, Structure $structure): ?ConstraintViolationListInterface
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
        $user->setRole($this->roleManager->getStructureAdminRole());
        $user->setStructure($structure);
        $this->em->persist($user);

        $errors = $this->validator->validate($user);
        if (count($errors)) {
            return $errors;
        }

        $this->em->flush();

        return null;
    }

    public function saveAdmin(User $user, ?string $plainPassword, Role $role = null, ?Group $group = null): void
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
        if ($role) {
            $user->setRole($role);
        }
        if ($group) {
            $user->setGroup($group);
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }


    public function associateTypeToUserIds(Type $type, ?array $userIds)
    {
        if ($userIds === null) {
            return;
        }
        /** @var User[] $inStructureUsers */
        $inStructureUsers = $this->userRepository->findUsersByIds($type->getStructure(), $userIds);

        $type->setAssociatedUsers($inStructureUsers);

    }
}
