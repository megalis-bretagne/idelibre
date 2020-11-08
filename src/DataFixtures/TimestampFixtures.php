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
    const REFERENCE = 'Timestamp_';

    public function load(ObjectManager $manager)
    {
       $timestamp = new Timestamp();
       $timestamp->setContent('message sent')
           ->setTsa('valid');

       $this->addReference(self::REFERENCE . 'sent', $timestamp);

        $manager->flush();
    }



}
