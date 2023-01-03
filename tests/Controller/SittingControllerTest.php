<?php

namespace App\Tests\Controller;

use App\Entity\Sitting;
use App\Service\S3\S3Manager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\TypeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SittingControllerTest extends WebTestCase
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

        SittingStory::load();
        EmailTemplateStory::load();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Séances');
    }

    public function testSecretary()
    {
        $this->loginAsSecretaryLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Séances');
    }

    public function testAdd()
    {
        $this->client->disableReboot();
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

        $type = TypeStory::testTypeLS();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajouter une séance');

        $filesystem = new FileSystem();
        $filesystem->copy(
            __DIR__ . '/../resources/fichier.pdf',
            __DIR__ . '/../resources/convocation.pdf'
        );

        $fileConvocation = new UploadedFile(
            __DIR__ . '/../resources/convocation.pdf',
            'convocation.pdf',
            'application/pdf'
        );

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[type]'] = $type->getId();
        $form['sitting[date]'] = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $form['sitting[place]'] = 'place';
        $form['sitting[convocationFile]'] = $fileConvocation;

        $this->client->submit($form);

        $this->client->enableReboot();
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, [
            'name' => 'unUsedType',
        ]));
    }

    public function testEditUsers()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/actors');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Modifier la séance');
    }

    public function testEditProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/projects');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Modifier la séance');
    }

    public function testDelete()
    {
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject', 'deleteObjects'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $fakeS3Manager->method('deleteObjects')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

        $this->loginAsAdminLibriciel();
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneSittingBy([
            'name' => 'Conseil Libriciel',
        ]));
    }

    public function testDeleteSecretaryNotAuthorizedType()
    {
        $this->loginAsSecretaryLibriciel();
        $sitting = SittingStory::sittingBureauLibriciel();

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowInformation()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/information');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowActors()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/actors');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/projects');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testGetZipSeances()
    {
        $container = self::getContainer();
        $bag = $container->get('parameter_bag');

        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $zipDirectory = $bag->get('document_zip_directory') . $sitting->getStructure()->getId() . '/';

        $filesystem = new FileSystem();
        $filesystem->copy(
            __DIR__ . '/../resources/fichier.zip',
            $zipDirectory . $sitting->getId() . '.zip'
        );

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/zip/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->client->getResponse();

        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020.zip"', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }

    public function testGetZipSeancesWrongStructure()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsUserMontpellier();
        $this->client->request(Request::METHOD_GET, '/sitting/zip/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetPdfSeances()
    {
        $container = self::getContainer();
        $bag = $container->get('parameter_bag');

        $sitting = SittingStory::sittingConseilLibriciel();

        $zipDirectory = $bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId() . '/';

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', $zipDirectory . $sitting->getId() . '.pdf');

        $this > self::assertFileExists($zipDirectory . $sitting->getId() . '.pdf');

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/pdf/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->client->getResponse();
        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020.pdf"', $response->headers->get('content-disposition'));
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }

    public function testEditInformation()
    {
        $this->client->disableReboot();
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', '/tmp/convocation');
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $item = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[place]'] = 'MyUniquePlace';

        $this->client->submit($form);

        $this->client->enableReboot();
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['place' => 'MyUniquePlace']));
    }

    public function testArchiveSeance()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/sitting/archive/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("La séance a été classée")');
        $this->assertCount(1, $successMsg);

        $item = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $item);
    }
}
