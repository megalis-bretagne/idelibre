<?php

namespace App\DataFixtures;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LsMessageConnectorFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'LsmessageFixtures_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        $lsmessageConnectorLibriciel = new LsmessageConnector($structureLibriciel);
        $lsmessageConnectorLibriciel->setApiKey("apikey")
        ->setUrl('https://url.fr')
        ->setSender("sender")
        ->setContent("my content")
        ->setActive(true);

        $manager->persist($lsmessageConnectorLibriciel);

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
