<?php

namespace App\Tests\Controller;

use App\Tests\Factory\FileFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\FileStory;
use App\Tests\Story\ProjectStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FileControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        FileStory::load();
        ProjectStory::load();
    }

    private function prepareFile(): string
    {
        $file = FileFactory::new([
            'name' => 'projet',
            'size' => 100,
            'path' => __DIR__ . '/../resources/fichier.pdf',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ])->create();

        $project = ProjectStory::project1();
        $project->setFile($file->object());

        $file->save();
        $project->save();

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
}
