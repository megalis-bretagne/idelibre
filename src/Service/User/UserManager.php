<?php


namespace App\Service\User;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function save(User $user, ?string $plainPassword, Structure $structure)
    {
        if ($plainPassword) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        }

        $user->setStructure($structure);
        $this->em->persist($user);

        $this->em->flush();
    }


    public function saveAdmin(User $user, ?string $plainPassword, Role $role = null, ?Group $group = null)
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


    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
