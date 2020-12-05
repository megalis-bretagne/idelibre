<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture
{
    public const REFERENCE = 'group_';

    public function load(ObjectManager $manager): void
    {
        $group = new Group();

        $group->setName('Recia');
        $manager->persist($group);

        $this->addReference(self::REFERENCE . 'recia', $group);

        $manager->flush();
    }
}
