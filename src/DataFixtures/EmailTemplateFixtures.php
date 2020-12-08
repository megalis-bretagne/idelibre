<?php

namespace App\DataFixtures;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmailTemplateFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'EmailTemplate_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        /** @var Type $typeConseilLibriciel */
        $typeConseilLibriciel = $this->getReference(TypeFixtures::REFERENCE . 'conseilLibriciel');

        $emailTemplateConseilLs = new EmailTemplate();
        $emailTemplateConseilLs->setStructure($structureLibriciel)
            ->setType($typeConseilLibriciel)
            ->setName('Conseil Libriciel')
            ->setSubject('idelibre : une nouvelle convocation ...')
            ->setContent("Voici mon template pour les seance de type conseil de la struture libriciel");

        $manager->persist($emailTemplateConseilLs);
        $this->addReference(self::REFERENCE . 'emailTemplateConseilLs', $emailTemplateConseilLs);


        $emailTemplateSansTypeLs = new EmailTemplate();
        $emailTemplateSansTypeLs->setStructure($structureLibriciel)
            ->setName('Sans type Libriciel')
            ->setSubject('idelibre : une nouvelle convocation ...')
            ->setContent("Voici un template sans type associé appartenant à libriciel");

        $manager->persist($emailTemplateSansTypeLs);
        $this->addReference(self::REFERENCE . 'emailTemplateSansTypeLs', $emailTemplateSansTypeLs);


        $emailTemplateMtp = new EmailTemplate();
        $emailTemplateMtp->setStructure($structureMontpellier)
            ->setName('Sans type Montpellier')
            ->setSubject('idelibre : une nouvelle convocation ...')
            ->setContent("Voici un template sans type associé apartenant à Montpellier");

        $manager->persist($emailTemplateMtp);
        $this->addReference(self::REFERENCE . 'emailTemplateMtp', $emailTemplateMtp);

        $manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            TypeFixtures::class
        ];
    }
}
