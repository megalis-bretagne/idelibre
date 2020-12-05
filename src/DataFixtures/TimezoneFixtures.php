<?php

namespace App\DataFixtures;

use App\Entity\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TimezoneFixtures extends Fixture
{
    public const REFERENCE = 'Timezone_';

    public function load(ObjectManager $manager): void
    {
        $timeZone = new Timezone();
        $timeZone->setName('Europe/Paris');
        $manager->persist($timeZone);
        $this->addReference(self::REFERENCE . 'paris', $timeZone);

        $manager->flush();
    }
}
