<?php


namespace App\Service\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupManager
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private StructureRepository $structureRepository;
    private RoleManager $roleManager;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        StructureRepository $structureRepository,
        RoleManager $roleManager
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->structureRepository = $structureRepository;
        $this->roleManager = $roleManager;
    }

    public function save(Group $group)
    {
        $this->em->persist($group);
        $this->em->flush();
    }


    public function associateStructure(Group $group)
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
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
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

    public function delete(Group $group)
    {
        $this->em->remove($group);
        $this->em->flush();
    }
}
