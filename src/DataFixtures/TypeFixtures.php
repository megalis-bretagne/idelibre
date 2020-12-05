<?php

namespace App\DataFixtures;

use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Type_';

    public function load(ObjectManager $manager): void
    {

        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        /** @var User $actor1Libriciel */
        $actor1Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel2');

        /** @var User $actor2Libriciel */
        $actor2Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel1');




        $typeConseilLibriciel = new Type();
        $typeConseilLibriciel->setName('Conseil Communautaire Libriciel')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actor1Libriciel)
            ->addAssociatedUser($actor2Libriciel);
        $manager->persist($typeConseilLibriciel);
        $this->addReference(self::REFERENCE . 'conseilLibriciel', $typeConseilLibriciel);


        $typeBureauLibriciel = new Type();
        $typeBureauLibriciel->setName('Bureau Communautaire Libriciel')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actor1Libriciel);

        $manager->persist($typeBureauLibriciel);
        $this->addReference(self::REFERENCE . 'bureauLibriciel', $typeBureauLibriciel);



        $typeConseilMontpellier = new Type();
        $typeConseilMontpellier->setName('Conseil Municipal Montpellier')
            ->setStructure($structureMontpellier);
        $manager->persist($typeConseilMontpellier);
        $this->addReference(self::REFERENCE . 'conseilMontpellier', $typeConseilMontpellier);


        $testTypeLS = new Type();
        $testTypeLS->setName('unUsedType')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actor1Libriciel)
            ->addAssociatedUser($actor2Libriciel);
        $manager->persist($testTypeLS);
        $this->addReference(self::REFERENCE . 'unUsedType', $testTypeLS);


        $manager->flush();
    }


    public function getDependencies()
    {
        return [
           StructureFixtures::class,
           UserFixtures::class
       ];
    }
}
