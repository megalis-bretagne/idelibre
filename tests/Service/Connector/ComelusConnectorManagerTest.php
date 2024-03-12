<?php

namespace App\Tests\Service\Connector;

use App\Repository\Connector\ComelusConnectorRepository;
use App\Service\Connector\ComelusConnectorManager;
use App\Tests\Factory\ComelusConnectorFactory;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\ComelusApiWrapper\ComelusException;
use Libriciel\ComelusApiWrapper\ComelusWrapper;
use Libriciel\ComelusApiWrapper\Model\ComelusDocument;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ComelusConnectorManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ComelusConnectorManager $comelusConnectorManager;
    private ComelusConnectorRepository $comelusConnectorRepository;
    private ComelusDocument $comelusDocument;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->comelusConnectorRepository = self::getContainer()->get(ComelusConnectorRepository::class);
        $this->comelusConnectorManager = self::getContainer()->get(ComelusConnectorManager::class);

        self::ensureKernelShutdown();
    }


    public function testCreateConnector()
    {
        $structure = StructureStory::libriciel()->object();
        $this->comelusConnectorManager = self::getContainer()->get(ComelusConnectorManager::class);
        $this->comelusConnectorManager->createConnector($structure);
        $this->assertNotNull($this->comelusConnectorRepository->findOneBy(['structure' => $structure]));
    }

    public function testIsAlreadyCreated()
    {
        $structure = StructureStory::libriciel()->object();

        $this->comelusConnectorManager = self::getContainer()->get(ComelusConnectorManager::class);
        $created = $this->comelusConnectorManager->isAlreadyCreated($structure);
        $this->assertFalse($created);
    }

    public function testCheckApiKey()
    {
        $comelusWrapperMock = $this->getMockBuilder(ComelusWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainer();
        $container->set(ComelusWrapper::class, $comelusWrapperMock);

        $comelusConnectorManager = $container->get(ComelusConnectorManager::class);


        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 'a772bb210ba620a4d4';

        $checked = $comelusConnectorManager->checkApiKey($url, $apiKey);
        $this->assertTrue($checked);
    }



    public function testCheckApiKeyFalse()
    {

        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 1234;

        $checked = $this->comelusConnectorManager->checkApiKey($url, $apiKey);
        $this->assertFalse($checked);
    }

    public function testGetMailingList()
    {
        $comelusWrapperMock = $this->getMockBuilder(ComelusWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $comelusWrapperMock->method('getMailingLists')->willReturn([]);

        $container = self::getContainer();
        $container->set(ComelusWrapper::class, $comelusWrapperMock);

        $comelusConnectorManager = $container->get(ComelusConnectorManager::class);

        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 'a997931df22772bb210ba620a4d4';

        $mailingListArray = $comelusConnectorManager->getMailingLists($url, $apiKey);

        $this->assertIsArray($mailingListArray);
    }

    public function testComelusNotEnabled()
    {
        $structure = StructureFactory::createOne()->object();
        ComelusConnectorFactory::createOne(['structure' => $structure]);
        $sitting = SittingFactory::createOne([
            'structure' => $structure,
            'date' => new \DateTime('now'),
        ])->object();
        $this->comelusConnectorManager = self::getContainer()->get(ComelusConnectorManager::class);

        $this->expectException(BadRequestHttpException::class);
        $this->comelusConnectorManager->sendComelus($sitting);
    }

}
