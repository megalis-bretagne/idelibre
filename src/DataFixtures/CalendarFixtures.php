<?php

namespace App\DataFixtures;

use App\Entity\Calendar;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CalendarFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Calendar_';

    public function load(ObjectManager $manager): void
    {
        /** @var Type $typeConseilLibriciel */
        $typeConseilLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'conseilLibriciel');

        $calendarConseil = (new Calendar())
            ->setType($typeConseilLibriciel)
            ->setIsActive(true)
            ->setDuration(240);

        $manager->persist($calendarConseil);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TypeFixtures::class,
        ];
    }
}
