<?php

namespace App\Tests\Service\File;

use App\Service\File\FileManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\AnnexStory;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\FileStory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FileManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private FileManager $fileManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();
        $this->fileManager = $container->get('App\Service\File\FileManager');

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        FileStory::load();
        ProjectStory::load();
        ConvocationStory::load();
        AnnexStory::load();
        SittingStory::load();
    }

    public function testListFilesFromSitting()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $files = $this->fileManager->listFilesFromSitting($sitting->object());
        $this->assertCount(5, $files);
    }
}
