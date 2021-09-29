<?php

namespace App\DataFixtures;

use App\Entity\Reminder;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReminderFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Reminder_';

    public function load(ObjectManager $manager): void
    {
        /** @var Type $typeConseilLibriciel */
        $typeConseilLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'conseilLibriciel');

        $reminderConseil = (new Reminder())
            ->setType($typeConseilLibriciel)
            ->setIsActive(true)
            ->setDuration(240);

        $manager->persist($reminderConseil);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TypeFixtures::class,
        ];
    }
}
