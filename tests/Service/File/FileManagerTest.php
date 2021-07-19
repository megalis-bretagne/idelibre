<?php

namespace App\Tests\Service\File;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\Service\File\FileManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileManagerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var FileManager|object|null
     */
    private FileManager $fileManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();
        $this->fileManager = $container->get('App\Service\File\FileManager');

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            FileFixtures::class,
            ProjectFixtures::class,
            ConvocationFixtures::class,
            AnnexFixtures::class,
            SittingFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testListFilesFromSitting()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $files = $this->fileManager->listFilesFromSitting($sitting);
        $this->assertCount(5, $files);
    }
}
