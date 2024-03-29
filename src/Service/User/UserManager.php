<?php

namespace App\Service\User;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Password\PasswordStrengthMeter;
use App\Security\Password\ResetPassword;
use App\Service\role\RoleManager;
use App\Service\Subscription\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly RoleManager $roleManager,
        private readonly PasswordStrengthMeter $passwordStrengthMeter,
        private readonly ResetPassword $resetPassword,
        private readonly SubscriptionManager $subscriptionManager,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function save(User $user, ?string $plainPassword, ?Structure $structure): bool
    {
        $user->setStructure($structure);

        if ($plainPassword) {
            $success = $this->passwordStrengthMeter->checkPasswordEntropy($user, $plainPassword);

            if (false === $success) {
                return false;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        } else {
            $user = $this->setFirstPassword($user);
        }

        $user->setSubscription($this->subscriptionManager->add($user));


        $this->em->persist($user);
        $this->em->flush();

        if (empty($plainPassword)) {
            $this->resetPassword->sendEmailDefinePassword($user);
        }

        return true;
    }


    public function editUser(User $user, string $plainPassword = null): bool
    {
        if ($plainPassword) {
            $success = $this->passwordStrengthMeter->checkPasswordEntropy($user, $plainPassword);

            if (false === $success) {
                return false;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($user);
        $this->em->flush();

        return true;
    }

    public function saveStructureAdmin(User $user, Structure $structure): ?ConstraintViolationListInterface
    {
        $user = $this->setFirstPassword($user);

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

    /**
     * @throws EntityNotFoundException
     */
    public function saveAdmin(User $user, Role $role = null, ?Group $group = null, $resetPassword = false): void
    {
        if ($resetPassword) {
            $user = $this->setFirstPassword($user);
        }

        if ($role) {
            $user->setRole($role);
        }

        if ($group) {
            $user->setGroup($group);
        }

        $this->em->persist($user);
        $this->em->flush();

        if ($resetPassword) {
            $this->resetPassword->sendEmailDefinePassword($user);
        }
    }

    public function setFirstPassword(User $user): User
    {
        $user->setPassword('CHANGEZ-MOI');

        return $user;
    }

    public function delete(User $user): void
    {
        $this->removeDeputy($user);
        $this->removeTitular($user);
        $this->em->remove($user);
        $this->em->flush();
    }

    private function removeDeputy(User $user): void
    {
        if ($user->getDeputy()) {
            $user->setDeputy(null);
        }
    }

    private function removeTitular(User $user): void
    {
        if ($user->getRole()->getName() === 'Deputy' && $user->getTitular()) {
            $user->setTitular(null);
        }
    }


    public function preference(User $user, ?string $plainPassword = null): ?bool
    {
        if ($plainPassword) {
            $success = $this->passwordStrengthMeter->checkPasswordEntropy($user, $plainPassword);

            if (false === $success) {
                return false;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($user);
        $this->em->flush();

        if ($user->getSubscription()) {
            $this->subscriptionManager->update($user->getSubscription());
        }

        return true;
    }

    public function AlreadyTakenDeputies(Structure $structure, ?User $currentUser): array
    {
        $users = $this->userRepository->findActorsByStructure($structure)->getQuery()->getResult();
        $takenDeputies = [];
        foreach ($users as $user) {
            if ($user->getDeputy() !== null && $user !== $currentUser) {
                $takenDeputies[] = $user->getDeputy();
            }
        }

        return $takenDeputies;
    }
}
