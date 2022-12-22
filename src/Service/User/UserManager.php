<?php

namespace App\Service\User;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use App\Security\Password\PasswordStrengthMeter;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private RoleManager $roleManager,
        private PasswordStrengthMeter $passwordStrengthMeter,
    ) {
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

    public function preference(User $user, Structure $structure, ?string $plainPassword = null): ?bool
    {
        if ($plainPassword) {
            $success = $this->passwordStrengthMeter->checkPasswordEntropy($user, $plainPassword);

            if (false === $success) {
                return false;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->save($user, $plainPassword, $structure);

        return true;
    }
}
