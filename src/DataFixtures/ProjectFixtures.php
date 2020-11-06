<?php

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'Project_';

    public function load(ObjectManager $manager)
    {
        /** @var Sitting $sittingConseilLibriciel */
        $sittingConseilLibriciel = $this->getReference(SittingFixtures::REFERENCE . 'sittingConseilLibriciel');

        /** @var File $fileProject1 */
        $fileProject1 = $this->getReference(FileFixtures::REFERENCE . 'fileProject1');

        /** @var File $fileProject2 */
        $fileProject2 = $this->getReference(FileFixtures::REFERENCE . 'fileProject2');

        /** @var Theme $themeFinance */
        $themeFinance = $this->getReference(ThemeFixtures::REFERENCE . 'financeLibriciel');

        /** @var User $actorLibriciel1 */
        $actorLibriciel1 = $this->getReference(UserFixtures::REFERENCE . 'actorLibriciel1');


        $project1 = new Project();
        $project1->setRank(0)
            ->setFile($fileProject1)
            ->setName('Project 1')
            ->setSitting($sittingConseilLibriciel)
            ->setTheme($themeFinance)
            ->setReporter($actorLibriciel1);

        $manager->persist($project1);
        $this->addReference(self::REFERENCE . 'project1', $project1);


        $project2 = new Project();
        $project2->setRank(1)
            ->setFile($fileProject2)
            ->setName('Project 2')
            ->setSitting($sittingConseilLibriciel);

        $manager->persist($project2);
        $this->addReference(self::REFERENCE . 'project2', $project2);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SittingFixtures::class,
            TypeFixtures::class,
            ThemeFixtures::class,
            FileFixtures::class,
            UserFixtures::class
        ];
    }
}
