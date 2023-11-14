<?php

namespace App\Tests\Controller;

use App\Entity\EventLog\Action;
use App\Entity\Sitting;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\TypeFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\LsvoteConnectorStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
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
        LsvoteConnectorStory::load();
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
        $type = TypeStory::testTypeLS();

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'une séance")');
        $this->assertCount(1, $item);

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', __DIR__ . '/../resources/convocation.pdf');

        $fileConvocation = new UploadedFile(__DIR__ . '/../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');

        $form = $crawler->selectButton('Ajouter la séance')->form();

        $form['sitting[type]'] = $type->getId();
        $form['sitting[date]'] = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $form['sitting[place]'] = 'place';
        $form['sitting[convocationFile]'] = $fileConvocation;
        $form['sitting[reminder][isActive]'] = "0";

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['name' => 'unUsedType']));
    }

    public function testEditUsers()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/actors');

        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modification des destinataires de la séance ")');
        $this->assertCount(1, $item);
    }

    public function testEditProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId() . '/projects');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modification des projets de la séance ")');
        $this->assertCount(1, $item);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        $sitting = SittingStory::sittingConseilLibriciel();

        $sittingId = $sitting->getId();

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneSittingBy(['name' => 'Conseil Libriciel']));

        $logEvent = $this->getOneEventLog(["targetId" => $sittingId]);
        $this->assertNotEmpty($logEvent);
        $this->assertEquals(Action::SITTING_DELETE, $logEvent->getAction());
    }

    public function testDeleteSittingWithDeputy()
    {
        $this->loginAsAdminLibriciel();

        $structure = StructureStory::libriciel()->object();

        $type = TypeFactory::createOne([
            'structure' => $structure,
                'associatedUsers' => [
                    'associatedUser1' => UserStory::actorLibriciel1(),
                    'associatedUser2' => UserStory::actorWithDeputy(),
                ],
                'authorizedSecretaries' => [UserStory::secretaryLibriciel1()],
            ]);


        $sitting = SittingFactory::createOne([
            'name' => 'Urbanisme',
            'date' => new DateTime('2020-11-22'),
            'structure' => $structure,
            'convocationFile' => FileFactory::createOne(),
            'place' => 'Salle du conseil',
            'type' => $type,
        ]);

        $sittingId = $sitting->getId();

        $this->client->request(Request::METHOD_DELETE, '/sitting/delete/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneSittingBy(['name' => 'Urbanisme']));

        $logEvent = $this->getOneEventLog(["targetId" => $sittingId]);
        $this->assertNotEmpty($logEvent);
        $this->assertEquals(Action::SITTING_DELETE, $logEvent->getAction());
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
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowActors()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/show/' . $sitting->getId() . '/actors');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Détail de la séance")');
        $this->assertCount(1, $item);
    }

    public function testShowProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
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

        $zipDirectory = $bag->get('document_full_zip_directory') . $sitting->getStructure()->getId() . '/';

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

        $pdfDirectory = $bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId() . '/';

        $filesystem = new FileSystem();
        $filesystem->copy('tests/resources/fichier.pdf', $pdfDirectory . $sitting->getId() . '.pdf');

        $this > self::assertFileExists($pdfDirectory . $sitting->getId() . '.pdf');

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
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/edit/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modification des informations de la séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[place]'] = 'MyUniquePlace';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['place' => 'MyUniquePlace']));
    }

    public function testArchiveSeance()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_POST, '/sitting/archive/' . $sitting->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La séance a été classée")');
        $this->assertCount(1, $successMsg);

        $item = $crawler->filter('html:contains("Séances")');
        $this->assertCount(1, $item);

        $logEvent = $this->getOneEventLog(["targetId" => $sitting->getId()]);
        $this->assertNotEmpty($logEvent);
        $this->assertEquals(Action::SITTING_ARCHIVED, $logEvent->getAction());
    }
}
