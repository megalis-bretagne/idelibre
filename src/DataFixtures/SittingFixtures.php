<?php

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SittingFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Sitting_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        /** @var Type $typeConseilLibriciel */
        /** @var Type $typeBureauLibriciel */
        /** @var File $fileConvocationConseilLs */
        /** @var File $fileConvocationBureauLs */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');
        $typeConseilLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'conseilLibriciel');
        $typeBureauLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'bureauLibriciel');
        $fileConvocationConseilLs = $this->getReference(FileFixtures::REFERENCE . 'convocation');
        $fileConvocationBureauLs = $this->getReference(FileFixtures::REFERENCE . 'convocation2');

        $sittingConseilLibriciel = (new Sitting())
            ->setName('Conseil Libriciel')
            ->setDate(new DateTimeImmutable('2020-10-22'))
            ->setStructure($structureLibriciel)
            ->setFile($fileConvocationConseilLs)
            ->setPlace('Salle du conseil')
            ->setType($typeConseilLibriciel);
        $manager->persist($sittingConseilLibriciel);
        $this->addReference(self::REFERENCE . 'sittingConseilLibriciel', $sittingConseilLibriciel);

        $sittingBureauLibriciel = (new Sitting())
            ->setName('Bureau Libriciel')
            ->setDate(new DateTimeImmutable('2020-10-22'))
            ->setStructure($structureLibriciel)
            ->setFile($fileConvocationBureauLs)
            ->setPlace('Salle du conseil')
            ->setType($typeBureauLibriciel);
        $manager->persist($sittingBureauLibriciel);
        $this->addReference(self::REFERENCE . 'sittingBureauLibriciel', $sittingBureauLibriciel);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            TypeFixtures::class,
            FileFixtures::class,
        ];
    }
}
