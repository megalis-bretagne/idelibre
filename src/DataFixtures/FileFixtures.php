<?php

namespace App\DataFixtures;

use App\Entity\File;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FileFixtures extends Fixture
{
    public const REFERENCE = 'File_';

    public function load(ObjectManager $manager): void
    {
        $fileProject1 = new File();
        $fileProject1->setName('Fichier projet 1')
            ->setSize(100)
            ->setPath('/tmp/fileProject1');

        $manager->persist($fileProject1);

        $this->addReference(self::REFERENCE . 'fileProject1', $fileProject1);


        $fileProject2 = new File();
        $fileProject2->setName('Fichier projet 2')
            ->setSize(100)
            ->setPath('/tmp/fileProject2');

        $manager->persist($fileProject2);

        $this->addReference(self::REFERENCE . 'fileProject2', $fileProject2);


        $fileAnnex1 = new File();
        $fileAnnex1->setName('Fichier annexe 1')
            ->setSize(100)
            ->setPath('/tmp/fileAnnex1');

        $manager->persist($fileAnnex1);

        $this->addReference(self::REFERENCE . 'fileAnnex1', $fileAnnex1);


        $fileAnnex2 = new File();
        $fileAnnex2->setName('Fichier annexe 2')
            ->setSize(100)
            ->setPath('/tmp/fileAnnex1');

        $manager->persist($fileAnnex2);

        $this->addReference(self::REFERENCE . 'fileAnnex2', $fileAnnex2);


        $fileConvocation = new File();
        $fileConvocation->setName('fichier de convocation')
            ->setSize(100)
            ->setPath('/tmp/convocation');

        $manager->persist($fileConvocation);

        $this->addReference(self::REFERENCE . 'convocation', $fileConvocation);




        $manager->flush();
    }
}
