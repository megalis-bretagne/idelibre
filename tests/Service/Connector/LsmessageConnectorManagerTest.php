<?php

namespace App\Tests\Service\Connector;

use App\Repository\Connector\LsmessageConnectorRepository;
use App\Service\Connector\LsmessageConnectorManager;
use App\Tests\Story\LsmessageConnectorStory;
use App\Tests\Story\StructureStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LsmessageConnectorManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private LsmessageConnectorManager $lsmessageConnectorManager;
    private LsmessageConnectorRepository $lsmessageConnectorRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();

        $this->lsmessageConnectorManager = self::getContainer()->get(LsmessageConnectorManager::class);
        $this->lsmessageConnectorRepository = self::getContainer()->get(LsmessageConnectorRepository::class);

        self::ensureKernelShutdown();
    }

    public function testCheckApiKey()
    {
        $url = 'https://lsmessage.recette.libriciel.fr';
        $apiKey = '8da45828bb71ba4d677e8a1fb7f8b07216695641e90413e001699b87c42ae103a5c08b477270ca2ed98064e13c20d89c88ddae0a61dbdb55885e6e74';

        $checked = $this->lsmessageConnectorManager->checkApiKey($url, $apiKey);
        $this->assertIsArray($checked);
        $this->assertNotEmpty($checked);
    }

    public function testGetLsmessageConnector()
    {
        $structure = StructureStory::libriciel()->object();
        $lsmessageConnector = LsmessageConnectorStory::lsmessageConnectorLibriciel()->object();

        $connector = $this->lsmessageConnectorManager->getLsmessageConnector($structure);

        $this->assertSame($lsmessageConnector->getStructure()->getName(), $connector->getStructure()->getName());
    }
}
