<?php

namespace App\DataFixtures;

use App\Entity\Party;
use App\Entity\Structure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PartyFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Party_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        $partyMajority = new Party();
        $partyMajority->setName('Majorité')
            ->setStructure($structureLibriciel);

        $manager->persist($partyMajority);
        $this->addReference(self::REFERENCE . 'majorite', $partyMajority);

        $partyOpposition = new Party();
        $partyOpposition->setName('Opposition')
            ->setStructure($structureLibriciel);

        $manager->persist($partyOpposition);
        $this->addReference(self::REFERENCE . 'opposition', $partyOpposition);

        $partyMontpellier = new Party();
        $partyMontpellier->setName('Montpellier')
            ->setStructure($structureMontpellier);

        $manager->persist($partyMontpellier);
        $this->addReference(self::REFERENCE . 'montpellier', $partyMontpellier);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
           StructureFixtures::class,
       ];
    }
}
