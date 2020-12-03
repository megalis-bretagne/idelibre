<?php

namespace App\DataFixtures;

use App\Entity\Connector\ComelusConnector;
use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ComelusConnectorFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'ComelusConnectorFixtures_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        $comelusConnectorLibriciel = new ComelusConnector($structureLibriciel);
        $comelusConnectorLibriciel->setApiKey("apikey")
        ->setUrl('https://url.fr')
        ->setDescription("my description")
        ->setActive(true);

        $manager->persist($comelusConnectorLibriciel);

        $manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
        ];
    }
}
