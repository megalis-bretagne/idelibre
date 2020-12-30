<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const REFERENCE = 'Role_';

    public function load(ObjectManager $manager): void
    {
        $roleSuperAdmin = (new Role())
            ->setName('SuperAdmin')
            ->setPrettyName('Super administrateur')
            ->setIsInStructureRole(false)
            ->addComposite('ROLE_SUPERADMIN')
            ->addComposite('ROLE_MANAGE_STRUCTURES')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleSuperAdmin);
        $this->addReference(self::REFERENCE . 'superAdmin', $roleSuperAdmin);

        $roleGroupAdmin = (new Role())
            ->setName('GroupAdmin')
            ->setPrettyName('Administrateur de groupe')
            ->setIsInStructureRole(false)
            ->addComposite('ROLE_GROUP_ADMIN')
            ->addComposite('ROLE_MANAGE_STRUCTURES')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleGroupAdmin);
        $this->addReference(self::REFERENCE . 'groupAdmin', $roleGroupAdmin);

        $roleStructureAdmin = (new Role())
            ->setName('Admin')
            ->setPrettyName('Administrateur')
            ->setIsInStructureRole(true)
            ->addComposite('ROLE_STRUCTURE_ADMIN')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleStructureAdmin);
        $this->addReference(self::REFERENCE . 'structureAdmin', $roleStructureAdmin);

        $roleSecretary = (new Role())
        ->setName('Secretary')
            ->setPrettyName('Secretaire')
            ->addComposite('ROLE_SECRETARY')
            ->setIsInStructureRole(true);
        $manager->persist($roleSecretary);
        $this->addReference(self::REFERENCE . 'secretary', $roleSecretary);

        $roleActor = (new Role())
            ->setName('Actor')
            ->setPrettyName('Elu')
            ->setIsInStructureRole(true)
            ->addComposite('ROLE_ACTOR');
        $manager->persist($roleActor);
        $this->addReference(self::REFERENCE . 'actor', $roleActor);

        $manager->flush();
    }
}
