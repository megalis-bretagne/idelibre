<?php

namespace App\DataFixtures;

use App\Entity\Configuration;
use App\Entity\Structure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConfigurationFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'CONFIGURATION_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        $configurationLibriciel = (new Configuration())
            ->setStructure($structureLibriciel)
            ->setIsSharedAnnotation(true);

        $manager->persist($configurationLibriciel);


        $configurationMontpellier = (new Configuration())
            ->setStructure($structureMontpellier)
            ->setIsSharedAnnotation(true);

        $manager->persist($configurationMontpellier);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
        ];
    }
}
