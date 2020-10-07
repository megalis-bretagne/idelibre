<?php

namespace App\DataFixtures;

use App\Entity\Structure;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'Theme_';

    public function load(ObjectManager $manager)
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');


        // --- ROOT lvl -----
        $rootTheme = new Theme();
        $rootTheme->setName('ROOT')
            ->setStructure($structureLibriciel);

        $manager->persist($rootTheme);
        $this->addReference(self::REFERENCE . 'rootLibriciel', $rootTheme);

        // --- First lvl -----
        $financeTheme = new Theme();
        $financeTheme->setName('Finance')
            ->setParent($rootTheme)
            ->setStructure($structureLibriciel);

        $manager->persist($financeTheme);
        $this->addReference(self::REFERENCE . 'financeLibriciel', $financeTheme);


        $ecoleTheme = new Theme();
        $ecoleTheme->setName('Finance')
            ->setParent($rootTheme)
            ->setStructure($structureLibriciel);

        $manager->persist($ecoleTheme);
        $this->addReference(self::REFERENCE . 'ecoleLibriciel', $ecoleTheme);


        $rhTheme = new Theme();
        $rhTheme->setName('rh')
            ->setParent($rootTheme)
            ->setStructure($structureLibriciel);

        $manager->persist($rhTheme);
        $this->addReference(self::REFERENCE . 'rhLibriciel', $rhTheme);

        // --- second lvl -----
        $budgetTheme = new Theme();
        $budgetTheme->setName('budget')
            ->setParent($financeTheme)
            ->setStructure($structureLibriciel);

        $manager->persist($budgetTheme);
        $this->addReference(self::REFERENCE . 'budgetLibriciel', $budgetTheme);


        // ----Montpellier
        $rootThemeMtp = new Theme();
        $rootThemeMtp->setName('ROOT')
            ->setStructure($structureMontpellier);

        $manager->persist($rootThemeMtp);
        $this->addReference(self::REFERENCE . 'rootMontpellier', $rootThemeMtp);


        $urbanismeThemeMtp = new Theme();
        $urbanismeThemeMtp->setName('Urbanisme Montpellier')
            ->setParent($rootThemeMtp)
            ->setStructure($structureMontpellier);

        $manager->persist($urbanismeThemeMtp);
        $this->addReference(self::REFERENCE . 'urbanismeMontpellier', $urbanismeThemeMtp);


        $manager->flush();
    }


    public function getDependencies()
    {
        return [
            StructureFixtures::class
        ];
    }
}
