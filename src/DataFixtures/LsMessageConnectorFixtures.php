<?php

namespace App\DataFixtures;

use App\Entity\Connector\LsmessageConnector;
use App\Entity\Structure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LsMessageConnectorFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'LsmessageFixtures_';

    public function load(ObjectManager $manager): void
    {
        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        $lsmessageConnectorLibriciel = new LsmessageConnector($structureLibriciel);
        $lsmessageConnectorLibriciel->setApiKey('apikey')
        ->setUrl('https://url.fr')
        ->setSender('sender')
        ->setContent('my content')
        ->setActive(true);

        $manager->persist($lsmessageConnectorLibriciel);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
        ];
    }
}
