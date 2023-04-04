<?php

namespace App\Tests\Service\Connector;

use App\Entity\Connector\LsmessageConnector;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Service\Connector\LsmessageConnectorManager;
use App\Tests\Story\LsmessageConnectorStory;
use App\Tests\Story\StructureStory;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\LsMessageWrapper;
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

        $this->lsmessageConnectorRepository = self::getContainer()->get(LsmessageConnectorRepository::class);

        self::ensureKernelShutdown();
    }


    public function testCheckApiKey()
    {
        $lsmessageWrapperMock = $this->getMockBuilder(LsMessageWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsmessageWrapperMock->method('setUrl')->willReturn(null);
        $lsmessageWrapperMock->method('setApiKey')->willReturn(null);

        self::getContainer()->set(LsMessageWrapper::class, $lsmessageWrapperMock);
        $lsmessageConnectorManager = self::getContainer()->get(LsmessageConnectorManager::class);

        $url = 'https://lsmessage.fr';
        $apiKey = '8da458285e6e74';

        $checked = $lsmessageConnectorManager->checkApiKey($url, $apiKey);
        $this->assertIsArray($checked);
    }

    public function testCheckApiKeyNull()
    {
        $lsmessageWrapperMock = $this->getMockBuilder(LsMessageWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsmessageWrapperMock->method('setUrl')->willReturn(null);
        $lsmessageWrapperMock->method('setApiKey')->willThrowException(new LsMessageException("error"));

        self::getContainer()->set(LsMessageWrapper::class, $lsmessageWrapperMock);
        $lsmessageConnectorManager = self::getContainer()->get(LsmessageConnectorManager::class);


        $url = 'https://lsmessage.fr';
        $apiKey = '8da458285e6e74';

        $checked = $lsmessageConnectorManager->checkApiKey($url, $apiKey);
        $this->assertNull($checked);
    }

    public function testGetLsmessageConnector()
    {
        $structure = StructureStory::libriciel()->object();
        $lsmessageConnector = LsmessageConnectorStory::lsmessageConnectorLibriciel()->object();

        $this->lsmessageConnectorManager = self::getContainer()->get(LsmessageConnectorManager::class);
        $connector = $this->lsmessageConnectorManager->getLsmessageConnector($structure);

        $this->assertSame($connector->getStructure()->getName(), $lsmessageConnector->getStructure()->getName());
    }
}
