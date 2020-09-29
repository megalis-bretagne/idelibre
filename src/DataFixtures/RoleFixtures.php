<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    const REFERENCE = 'Role_';

    public function load(ObjectManager $manager)
    {
        $roleSuperAdmin = new Role();
        $roleSuperAdmin->setName('SuperAdmin')
            ->addComposite('ROLE_SUPERADMIN')
            ->addComposite('ROLE_MANAGE_STRUCTURES')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleSuperAdmin);
        $this->addReference(self::REFERENCE . 'superAdmin', $roleSuperAdmin);

        $roleGroupAdmin = new Role();
        $roleGroupAdmin->setName('GroupAdmin')
            ->addComposite('ROLE_GROUP_ADMIN')
            ->addComposite('ROLE_MANAGE_STRUCTURES')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleGroupAdmin);
        $this->addReference(self::REFERENCE . 'groupAdmin', $roleGroupAdmin);


        $roleStructureAdmin = new Role();
        $roleStructureAdmin->setName('Admin')
            ->addComposite('ROLE_STRUCTURE_ADMIN')
            ->addComposite('ROLE_MANAGE_USERS');
        $manager->persist($roleStructureAdmin);
        $this->addReference(self::REFERENCE . 'structureAdmin', $roleStructureAdmin);


        $roleSecretary = new Role();
        $roleSecretary->setName('Secretary');

        $manager->persist($roleSecretary);
        $this->addReference(self::REFERENCE . 'secretary', $roleSecretary);


        $manager->flush();
    }
}
