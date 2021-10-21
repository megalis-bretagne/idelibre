<?php

namespace App\DataFixtures;

use App\Entity\ApiRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApiRoleFixtures extends Fixture
{
    public const REFERENCE = 'ApiRole_';

    public function load(ObjectManager $manager): void
    {
        $roleApiStructureAdmin = new ApiRole();
        $roleApiStructureAdmin->setName("ApiStructureAdmin")
            ->setPrettyName('Administrateur api')
            ->setComposites(['ROLE_API_STRUCTURE_ADMIN']);

        $manager->persist($roleApiStructureAdmin);
        $this->addReference(self::REFERENCE . 'apiStructureAdmin', $roleApiStructureAdmin);

        $manager->flush();
    }

}
