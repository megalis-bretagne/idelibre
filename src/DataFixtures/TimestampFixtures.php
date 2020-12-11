<?php

namespace App\DataFixtures;

use App\Entity\Timestamp;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
