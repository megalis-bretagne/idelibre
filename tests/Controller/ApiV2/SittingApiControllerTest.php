<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TimestampFixtures;
use App\DataFixtures\TypeFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SittingApiControllerTest extends WebTestCase
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
            ApiUserFixtures::class,
            SittingFixtures::class,
            StructureFixtures::class,
            ConvocationFixtures::class,
            TimestampFixtures::class,
            FileFixtures::class,
            ProjectFixtures::class,
            AnnexFixtures::class,
            TypeFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testGetAll()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/sittings", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            "CONTENT_TYPE" => 'application/json'
        ]);

        $response = $this->client->getResponse();
        $sittings = json_decode($response->getContent(), true);

        $this->assertCount(2, $sittings);
    }


    public function testGetOne()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            "CONTENT_TYPE" => 'application/json'
        ]);

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);

        $this->assertNotEmpty($sitting);
        $this->assertSame($sittingConseil->getId(), $sitting['id']);
    }


    public function testGetAllConvocations()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);

        $this->client->request(Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/convocations",
            [], [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                "CONTENT_TYPE" => 'application/json'
            ]);

        $response = $this->client->getResponse();
        $convocations = json_decode($response->getContent(), true);

        $this->assertCount(2, $convocations);
    }


    public function testGetAllProjects()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);

        $this->client->request(Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects",
            [], [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                "CONTENT_TYPE" => 'application/json'
            ]);

        $response = $this->client->getResponse();
        $projects = json_decode($response->getContent(), true);

        $this->assertCount(2, $projects);
    }


    public function testAddSitting()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/invitation.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation.pdf');



        $invitationFile = new UploadedFile(__DIR__ . '/../../resources/invitation.pdf', 'invitation.pdf', 'application/pdf');
        $convocationFile = new UploadedFile(__DIR__ . '/../../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');




        $this->client->request(Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings",
            [
                'date' => '2020-10-22 11:00:00',
                'type' => $type->getId(),
                'place' => 'salle du conseil'
            ],
            [
               'invitationFile' => $invitationFile,
               'convocationFile' => $convocationFile
            ],
            [
                "HTTP_ACCEPT" => 'application/json',
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            ],
        );

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);

        $this->assertNotEmpty($sitting['id']);
        $this->assertSame($sitting['name'], "Conseil Communautaire Libriciel");
        $this->assertNotEmpty($sitting['convocationFile']);
        $this->assertNotEmpty($sitting['invitationFile']);
    }



    public function testUpdateSitting()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);
       // $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/invitation_updated.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation_updated.pdf');



        $invitationFile = new UploadedFile(__DIR__ . '/../../resources/invitation_updated.pdf', 'invitation_updated.pdf', 'application/pdf');
        $convocationFile = new UploadedFile(__DIR__ . '/../../resources/convocation_updated.pdf', 'convocation_updated.pdf', 'application/pdf');


        $this->client->request(Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}",
            [
                'date' => '2020-10-22 17:30:00',
                'place' => 'salle de la mairie'
            ],
            [
               'invitationFile' => $invitationFile,
               'convocationFile' => $convocationFile
            ],
            [
                "HTTP_ACCEPT" => 'application/json',
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            ],
        );

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);
dd($sitting);

        $this->assertNotEmpty($sitting['id']);
        $this->assertSame($sitting['name'], "Conseil Communautaire Libriciel");
        $this->assertNotEmpty($sitting['convocationFile']);
        $this->assertNotEmpty($sitting['invitationFile']);
    }

}
