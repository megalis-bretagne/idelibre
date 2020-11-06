<?php

namespace App\DataFixtures;

use App\Entity\Annex;
use App\Entity\File;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AnnexFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'Annex_';

    public function load(ObjectManager $manager)
    {
        /** @var File $fileAnnex1 */
        $fileAnnex1 = $this->getReference(FileFixtures::REFERENCE . 'fileAnnex1');

        /** @var File $fileAnnex2 */
        $fileAnnex2 = $this->getReference(FileFixtures::REFERENCE . 'fileAnnex2');

        /** @var Project $project1 */
        $project1 = $this->getReference(ProjectFixtures::REFERENCE . 'project1');


        $annex1 = new Annex();
        $annex1->setFile($fileAnnex1)
            ->setRank(0)
            ->setProject($project1);

        $manager->persist($annex1);
        $this->addReference(self::REFERENCE . 'annex1', $annex1);


        $annex2 = new Annex();
        $annex2->setFile($fileAnnex2)
            ->setRank(1)
            ->setProject($project1);

        $manager->persist($annex2);
        $this->addReference(self::REFERENCE . 'annex2', $annex2);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            FileFixtures::class,
            ProjectFixtures::class
        ];
    }
}
