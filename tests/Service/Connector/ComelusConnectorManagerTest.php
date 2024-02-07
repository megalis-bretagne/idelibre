<?php

namespace App\Tests\Service\Connector;

use App\Repository\Connector\ComelusConnectorRepository;
use App\Service\Connector\Comelus\ComelusConnectorManager;
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

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->comelusConnectorRepository = self::getContainer()->get(ComelusConnectorRepository::class);

        self::ensureKernelShutdown();
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

        $comelusWrapperMock->method('setApiKey')->willReturn(null);
        $comelusWrapperMock->method('setUrl')->willReturn(null);
        $comelusWrapperMock->method('check')->willReturn([]);

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
        $comelusWrapperMock = $this->getMockBuilder(ComelusWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $comelusWrapperMock->method('setApiKey')->willReturn(null);
        $comelusWrapperMock->method('setUrl')->willThrowException(new ComelusException("bad url format"));
        $comelusWrapperMock->method('check')->willReturn([]);

        $container = self::getContainer();
        $container->set(ComelusWrapper::class, $comelusWrapperMock);

        $comelusConnectorManager = $container->get(ComelusConnectorManager::class);

        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 'a772bb210ba620a4d4';

        $checked = $comelusConnectorManager->checkApiKey($url, $apiKey);
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

    public function testSendComelus()
    {
        $comelusWrapperMock = $this->getMockBuilder(ComelusWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $comelusWrapperMock->method('createDocument')->willReturn(['id' => '286bf9f6-668f-4724-a2b8-aab79048950b']);

        $container = self::getContainer();
        $container->set(ComelusWrapper::class, $comelusWrapperMock);
        $comelusConnectorManager = $container->get(ComelusConnectorManager::class);

        $uuid_regex = ' ^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}^ ';

        $filesystem = new Filesystem();

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', 'tests/resources/fichier.pdf');

        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/fichier.pdf', 'fichier.pdf', 'application/pdf');

        $file1 = FileFactory::createOne([
            'name' => 'Convocation',
            'size' => 100,
            'path' => $fileProject1->getPath() . '/' . $fileProject1->getFilename(),
        ])->object();

        $structure = StructureStory::libriciel()->object();
        $sitting = SittingFactory::createOne([
            'name' => 'Conseil',
            'date' => new DateTime('2020-10-22'),
            'structure' => $structure,
            'convocationFile' => $file1,
            'place' => 'Mairie',
            'type' => TypeStory::typeConseilLibriciel(),
        ])->object();
        ComelusConnectorFactory::createOne([
            'structure' => $structure,
            'url' => 'https://comelus.dev.libriciel.net',
            'apiKey' => 'dsfdsdsfdsfdsfdsf',
            'active' => true,
            'description' => 'lorem ipsum',
            'mailingListId' => '3017fc63-7bca-4020-a51d-1daa760baf18',
        ]);

        $this->comelusConnectorRepository->findOneBy(['structure' => $structure]);
        $comelusId = $comelusConnectorManager->sendComelus($sitting);

        $this->assertTrue(is_string($comelusId) && preg_match($uuid_regex, $comelusId));
    }
}
