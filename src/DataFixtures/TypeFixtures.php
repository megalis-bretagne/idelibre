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
        /** @var Structure $structureLibriciel
         * @var Structure $structureMontpellier
         * @var User      $actorLibriciel1
         * @var User      $actorLibriciel2
         * @var User      $secretaryLibriciel1
         */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');
        $actorLibriciel1 = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel1');
        $actorLibriciel2 = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel2');
        $secretaryLibriciel1 = $this->getReference(UserFixtures::REFERENCE . 'secretaryLibriciel1');

        $typeConseilLibriciel = (new Type())
            ->setName('Conseil Communautaire Libriciel')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actorLibriciel1)
            ->addAssociatedUser($actorLibriciel2)
            ->addAuthorizedSecretary($secretaryLibriciel1);
        $manager->persist($typeConseilLibriciel);
        $this->addReference(self::REFERENCE . 'conseilLibriciel', $typeConseilLibriciel);

        $typeBureauLibriciel = (new Type())
            ->setName('Bureau Communautaire Libriciel')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actorLibriciel2);
        $manager->persist($typeBureauLibriciel);
        $this->addReference(self::REFERENCE . 'bureauLibriciel', $typeBureauLibriciel);

        $typeConseilMontpellier = (new Type())
            ->setName('Conseil Municipal Montpellier')
            ->setStructure($structureMontpellier);
        $manager->persist($typeConseilMontpellier);
        $this->addReference(self::REFERENCE . 'conseilMontpellier', $typeConseilMontpellier);

        $testTypeLS = (new Type())
            ->setName('unUsedType')
            ->setStructure($structureLibriciel)
            ->addAssociatedUser($actorLibriciel1)
            ->addAssociatedUser($actorLibriciel2);
        $manager->persist($testTypeLS);
        $this->addReference(self::REFERENCE . 'unUsedType', $testTypeLS);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StructureFixtures::class,
            UserFixtures::class,
        ];
    }
}
