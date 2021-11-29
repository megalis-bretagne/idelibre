<?php

namespace App\DataFixtures;

use App\Entity\ApiRole;
use App\Entity\ApiUser;
use App\Entity\Structure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ApiUserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'ApiUser_';

    public function load(ObjectManager $manager): void
    {
        /**
         * @var Structure $structureLibriciel
         * @var Structure $structureMontpellier
         * @var ApiRole   $roleApiStructureAdmin
         */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');
        $roleApiStructureAdmin = $this->getReference(ApiRoleFixtures::REFERENCE . 'apiStructureAdmin');

        //////  apiStructureAdmin ///////

        $apiAdminLibriciel = (new ApiUser())
            ->setApiRole($roleApiStructureAdmin)
            ->setStructure($structureLibriciel)
            ->setName('connecteur api')
            ->setToken('1234');
        $manager->persist($apiAdminLibriciel);
        $this->addReference(self::REFERENCE . 'apiAdminLibriciel', $apiAdminLibriciel);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            ApiRoleFixtures::class,
        ];
    }
}
