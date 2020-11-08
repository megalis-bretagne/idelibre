<?php

namespace App\Tests\Controller;

use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\Entity\File;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class FileControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->loadFixtures([
            FileFixtures::class,
            ProjectFixtures::class
        ]);
    }

    private function prepareFile(): string
    {
        $file = new File();
        $file->setName('projet')
            ->setSize(100)
            ->setPath(__DIR__ . '/../resources/fichier.pdf');

        $project = $this->getOneProjectBy(['name' => 'Project 1']);
        $project->setFile($file);

        $this->entityManager->persist($file);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $file->getId();
    }

    public function testDownload()
    {
        $fileId = $this->prepareFile();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/file/download/' . $fileId);
        $this->assertResponseStatusCodeSame(200);
    }


    public function testDownloadLoginWrongStructure()
    {
        $fileId = $this->prepareFile();

        $this->loginAsUserMontpellier();
        $this->client->request(Request::METHOD_GET, '/file/download/' . $fileId);
        $this->assertResponseStatusCodeSame(403);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }
}
