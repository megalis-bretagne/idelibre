<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StructureFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'STRUCTURE_';

    public function load(ObjectManager $manager): void
    {
        /** @var Group $groupRecia */
        $groupRecia = $this->getReference(GroupFixtures::REFERENCE . 'recia');

        /** @var Timezone $timezoneParis */
        $timezoneParis = $this->getReference(TimezoneFixtures::REFERENCE . 'paris');

        $structureLibriciel = new Structure();
        $structureLibriciel->setName('Libriciel')
            ->setSuffix('libriciel')
            ->setReplyTo('libriciel@example.org')
            ->setTimezone($timezoneParis);

        $manager->persist($structureLibriciel);
        $this->addReference(self::REFERENCE . 'libriciel', $structureLibriciel);

        $structureMtp = new Structure();
        $structureMtp->setName('Montpellier')
            ->setSuffix('montpellier')
            ->setReplyTo('montpellier@example.org')
            ->setTimezone($timezoneParis)
            ->setGroup($groupRecia);
        $manager->persist($structureMtp);
        $this->addReference(self::REFERENCE . 'montpellier', $structureMtp);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            TimezoneFixtures::class,
            GroupFixtures::class,
        ];
    }
}
