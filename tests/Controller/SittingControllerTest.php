<?php

namespace App\Tests\Controller;

use App\DataFixtures\FileFixtures;
use App\DataFixtures\SittingFixtures;
use App\Entity\Sitting;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SittingControllerTest extends WebTestCase
{
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

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            SittingFixtures::class,
            FileFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $item);
    }

    public function testSecretary()
    {
        $this->loginAsSecretaryLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $type = $this->getOneTypeBy(['name' => 'unUsedType']);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter une séance")');
        $this->assertCount(1, $item);

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', __DIR__ . '/../resources/convocation.pdf');

        $fileConvocation = new UploadedFile(__DIR__ . '/../resources/convocation.pdf', 'fichier.pdf', 'application/pdf');

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[type]'] = $type->getId();
        $form['sitting[date]'] = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $form['sitting[place]'] = 'place';
        $form['sitting[convocationFile]'] = $fileConvocation;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['name' => 'unUsedType']));
    }

    public function testEditUsers()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/actors');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $item);
    }

    public function testEditProjects()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/projects');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $item);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneSittingBy(['name' => 'Conseil Libriciel']));
    }

    public function testDeleteSecretaryNotAuthorizedType()
    {
        $this->loginAsSecretaryLibriciel();
        $sitting = $this->getOneSittingBy(['name' => 'Bureau Libriciel']);

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowInformation()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/information');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowActors()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/actors');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowProjects()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/projects');
        $this->assertResponseStatusCodeSame(200);

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
        $filesystem->copy(__DIR__ . '/../resources/fichier.zip', $zipDirectory . $sitting->getId() . '.zip');

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/zip/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);


        $response = $this->client->getResponse();

        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020.zip"', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));

    }

    public function testGetZipSeancesWrongStructure()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $this->loginAsUserMontpellier();
        $this->client->request(Request::METHOD_GET, '/sitting/zip/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetPdfSeances()
    {
        $container = self::getContainer();
        $bag = $container->get('parameter_bag');

        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $zipDirectory = $bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId() . '/';

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', $zipDirectory . $sitting->getId() . '.pdf');

        $this>self::assertFileExists($zipDirectory . $sitting->getId() . '.pdf');

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/sitting/pdf/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020.pdf"', $response->headers->get('content-disposition'));
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }

    public function testEditInformation()
    {
        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', '/tmp/convocation');
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[place]'] = 'MyUniquePlace';

        $crawler = $this->client->submit($form);


        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Modifier la séance")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['place' => 'MyUniquePlace']));
    }

    public function testArchiveSeance()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/sitting/archive/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La séance a été classée")');
        $this->assertCount(1, $successMsg);

        $item = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $item);
    }
}
