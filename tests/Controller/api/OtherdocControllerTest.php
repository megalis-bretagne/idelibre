<?php

namespace App\Tests\Controller\api;

use App\Entity\Otherdoc;
use App\Service\ApiEntity\OtherdocApi;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\OtherdocFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use App\Tests\StringTrait;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class OtherdocControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use StringTrait;
    use LoginTrait;
    use FindEntityTrait;

    private ?KernelBrowser $client;
    private EntityManager $entityManager;
    private Serializer $serializer;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->serializer = self::getContainer()->get('serializer');

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testEdit()
    {
        UserStory::load();
        SittingStory::load();
        $structure = StructureStory::libriciel();
        $sitting = SittingFactory::createOne([
            "structure" => $structure,
            "date" => new DateTime()
        ]);

        $this->loginAsAdminLibriciel();


        $filesystem = new Filesystem();

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/convocation.pdf');

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/document.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/document2.pdf');
        $otherdocFile1 = new UploadedFile(__DIR__ . '/../../resources/document.pdf', 'fichier.pdf', 'application/pdf');
        $otherdocFile2 = new UploadedFile(__DIR__ . '/../../resources/document2.pdf', 'fichier.pdf', 'application/pdf');

        $otherdoc1 = new OtherdocApi();
        $otherdoc1
            ->setName('otherDocument')
            ->setFileName('document.pdf')
            ->setLinkedFileKey('document')
            ->setRank(1)
            ->setSize(100);

        $otherdoc2 = new OtherdocApi();
        $otherdoc2
            ->setName('otherDocument2')
            ->setFileName('document2.pdf')
            ->setLinkedFileKey('document2')
            ->setRank(2)
            ->setSize(100);
        $serializedOtherdocs = $this->serializer->serialize([$otherdoc1, $otherdoc2], 'json');


        $this->client->request(
            Request::METHOD_POST,
            '/api/otherdocs/' . $sitting->getId(),
            ['otherdocs' => $serializedOtherdocs],
            [
                'document' => $otherdocFile1,
                'document2' => $otherdocFile2,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($this->client->getResponse()->getContent());

        $this->assertTrue($content->success);

        $otherDocument = $this->getOneEntityBy(Otherdoc::class, ['name' => 'otherDocument']);
        $this->assertNotEmpty($otherDocument);
    }


    public function testGetOtherdocsFromSitting()
    {

        UserStory::load();
        $sitting = SittingFactory::createOne([
            "structure" => StructureStory::libriciel(),
            "date" => new DateTime()
        ]);

        $file1 = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100,
        ]);
        $file2 = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100,
        ]);

        OtherdocFactory::createOne(['file' => $file1, 'sitting' => $sitting, "rank" => 0]);
        OtherdocFactory::createOne(['file' => $file2, 'sitting' => $sitting]);

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/otherdocs/{$sitting->getId()}");

        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(2, $content);

    }

}