<?php

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SittingFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'Sitting_';

    public function load(ObjectManager $manager)
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        /** @var Type $typeConseilLibriciel */
        $typeConseilLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'conseilLibriciel');

        /** @var File $fileConvocationConseilLs */
        $fileConvocationConseilLs = $this->getReference(FileFixtures::REFERENCE . 'convocation');

        $sittingConseilLibriciel = new Sitting();
        $sittingConseilLibriciel->setName('Conseil Libriciel')
            ->setDate(new \DateTimeImmutable())
            ->setStructure($structureLibriciel)
            ->setFile($fileConvocationConseilLs)
            ->setType($typeConseilLibriciel);

        $manager->persist($sittingConseilLibriciel);
        $this->addReference(self::REFERENCE . 'sittingConseilLibriciel', $sittingConseilLibriciel);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            TypeFixtures::class,
            FileFixtures::class
        ];
    }
}
