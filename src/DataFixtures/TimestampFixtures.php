<?php

namespace App\DataFixtures;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TimestampFixtures extends Fixture
{
    public const REFERENCE = 'Timestamp_';

    public function load(ObjectManager $manager): void
    {
        $timestamp = new Timestamp();
        $timestamp->setFilePathContent('fake path File content')
           ->setFilePathTsa('fakepathFile tsa');

        $this->addReference(self::REFERENCE . 'sent', $timestamp);

        $manager->flush();
    }
}
