<?php

namespace App\DataFixtures;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConvocationFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'Convocation';

    public function load(ObjectManager $manager)
    {
        /** @var Sitting $sittingConseilLibriciel */
        $sittingConseilLibriciel = $this->getReference(SittingFixtures::REFERENCE . 'sittingConseilLibriciel');

        /** @var User $actor1Libriciel */
        $actor1Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel2');

        /** @var User $actor2Libriciel */
        $actor2Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel1');

        $convocationActor1 = (new Convocation())
            ->setSitting($sittingConseilLibriciel)
            ->setActor($actor1Libriciel);
        $manager->persist($convocationActor1);
        $this->addReference(self::REFERENCE . 'convocationActor1Conseil', $convocationActor1);


        $convocationActor2 = (new Convocation())
            ->setSitting($sittingConseilLibriciel)
            ->setActor($actor2Libriciel);
        $manager->persist($convocationActor2);
        $this->addReference(self::REFERENCE . 'convocationActor2Conseil', $convocationActor2);


        $manager->flush();
    }


    public function getDependencies()
    {
        return [
           StructureFixtures::class,
           UserFixtures::class,
           SittingFixtures::class
       ];
    }
}
