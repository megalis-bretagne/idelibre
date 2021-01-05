<?php

namespace App\DataFixtures;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConvocationFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'Convocation_';

    public function load(ObjectManager $manager): void
    {
        /** @var Sitting $sittingConseilLibriciel */
        $sittingConseilLibriciel = $this->getReference(SittingFixtures::REFERENCE . 'sittingConseilLibriciel');

        /** @var User $actor1Libriciel */
        $actor1Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel1');

        /** @var User $actor2Libriciel */
        $actor2Libriciel = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel2');

        /** @var Timestamp $timestamp */
        $timestamp = $this->getReference(TimestampFixtures::REFERENCE . 'sent');

        $convocationActor1 = (new Convocation())
            ->setSitting($sittingConseilLibriciel)
            ->setUser($actor1Libriciel)
            ->setCategory(Convocation::CATEGORY_CONVOCATION);
        $manager->persist($convocationActor1);
        $this->addReference(self::REFERENCE . 'convocationActor1Conseil', $convocationActor1);

        $convocationActor2Sent = (new Convocation())
            ->setSitting($sittingConseilLibriciel)
            ->setUser($actor2Libriciel)
            ->setSentTimestamp($timestamp)
            ->setCategory(Convocation::CATEGORY_CONVOCATION);

        $manager->persist($convocationActor2Sent);
        $this->addReference(self::REFERENCE . 'convocationActor2Conseil', $convocationActor2Sent);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            UserFixtures::class,
            SittingFixtures::class,
            TimestampFixtures::class,
        ];
    }
}
