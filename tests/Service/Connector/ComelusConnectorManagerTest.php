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

        $container = self::getContainer();

        $this->comelusConnectorManager = self::getContainer()->get(ComelusConnectorManager::class);
        $this->comelusConnectorRepository = self::getContainer()->get(ComelusConnectorRepository::class);

        self::ensureKernelShutdown();
    }

    public function testIsAlreadyCreated()
    {
        $structure = StructureStory::libriciel()->object();

        $created = $this->comelusConnectorManager->isAlreadyCreated($structure);
        $this->assertFalse($created);
    }

    public function testCheckApiKey()
    {
        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 'a997931d6491b4c393be737bdcbdb7eb6db76d07fd92f85acacff81962ce5c3d4ca73b1f65c434deca72873f0c0a1a83f60cf22772bb210ba620a4d4';

        $checked = $this->comelusConnectorManager->checkApiKey($url, $apiKey);
        $this->assertTrue($checked);
    }

    public function testGetMailingList()
    {
        $url = 'https://comelus.dev.libriciel.net';
        $apiKey = 'a997931d6491b4c393be737bdcbdb7eb6db76d07fd92f85acacff81962ce5c3d4ca73b1f65c434deca72873f0c0a1a83f60cf22772bb210ba620a4d4';

        $mailingListArray = $this->comelusConnectorManager->getMailingLists($url, $apiKey);

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

        $this->expectException(BadRequestHttpException::class);
        $this->comelusConnectorManager->sendComelus($sitting);
    }

    public function testSendComelus()
    {
        $uuid_regex = ' ^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}^ ';

        $filesystem = new Filesystem();
        $filesystem->copy('tests/resources/fichier.pdf', 'tests/resources/fichier.pdf');

        $fileProject1 = new UploadedFile('tests/resources/fichier.pdf', 'fichier.pdf', 'application/pdf');

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
            'apiKey' => 'a997931d6491b4c393be737bdcbdb7eb6db76d07fd92f85acacff81962ce5c3d4ca73b1f65c434deca72873f0c0a1a83f60cf22772bb210ba620a4d4',
            'active' => true,
            'description' => 'lorem ipsum',
            'mailingListId' => '3017fc63-7bca-4020-a51d-1daa760baf18',
        ]);

        $this->comelusConnectorRepository->findOneBy(['structure' => $structure]);
        $comelusId = $this->comelusConnectorManager->sendComelus($sitting);

        $this->assertTrue(is_string($comelusId) && preg_match($uuid_regex, $comelusId));
    }
}
