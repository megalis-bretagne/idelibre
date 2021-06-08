<?php

namespace App\Service\User;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private RoleManager $roleManager;
    private TypeRepository $typeRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        RoleManager $roleManager,
        TypeRepository $typeRepository
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->roleManager = $roleManager;
        $this->typeRepository = $typeRepository;
    }

    public function save(User $user, ?string $plainPassword, ?Structure $structure): void
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        }

        $user->setStructure($structure);
        $this->updateAuthorizedType($user);

        $this->em->persist($user);

        $this->em->flush();
    }

    public function saveStructureAdmin(User $user, ?string $plainPassword, Structure $structure): ?ConstraintViolationListInterface
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
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
            $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
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

    private function updateAuthorizedType(User $user)
    {
        $this->removeAuthorizedType($user);
        $this->addAuthorizedType($user);
    }

    private function addAuthorizedType(User $user)
    {
        foreach ($user->getAuthorizedTypes() as $type) {
            $type->addAuthorizedSecretary($user);
        }
    }

    private function removeAuthorizedType(User $user)
    {
        $authorizedTypes = $this->typeRepository->findAuthorizedTypeByUser($user)->getQuery()->getResult();

        foreach ($authorizedTypes as $authorizedType) {
            $authorizedType->removeAuthorizedSecretary($user);
        }
    }
}
