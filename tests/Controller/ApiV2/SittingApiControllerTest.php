<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\TimestampFixtures;
use App\DataFixtures\TypeFixtures;
use App\Service\ApiEntity\AnnexApi;
use App\Service\ApiEntity\ProjectApi;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

class SittingApiControllerTest extends WebTestCase
{

    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    private Serializer $serializer;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->serializer = $kernel->getContainer()
            ->get('serializer');

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
            TypeFixtures::class,
            ThemeFixtures::class
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

        $this->assertCount(3, $sittings);
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

        $this->assertResponseStatusCodeSame(200);

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

        $this->assertResponseStatusCodeSame(200);

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

        $this->assertSame($sittingConseil->getId(), $sitting['id']);
        $this->assertSame("Conseil Libriciel", $sitting['name']);
        $this->assertSame('convocation_updated.pdf', $sitting['convocationFile']['name']);
        $this->assertSame('invitation_updated.pdf', $sitting['invitationFile']['name']);
        $this->assertNotEmpty($sitting['invitationFile']);
    }


    public function testAddProjectsToSitting()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingBureau = $this->getOneSittingBy(['name' => 'Bureau Libriciel sans projets', 'structure' => $structure]);
        $themeBudget = $this->getOneThemeBy(['fullName' => "Finance, budget"]);

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1_project2.pdf');


        $project1File = new UploadedFile(__DIR__ . '/../../resources/project1.pdf', 'project1.pdf', 'application/pdf');
        $project2File = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'project2.pdf', 'application/pdf');
        $annex1Project2File = new UploadedFile(__DIR__ . '/../../resources/annex1_project2.pdf', 'annex1_project2.pdf', 'application/pdf');


        $project1 = (new ProjectApi())
            ->setName('project1')
            ->setFileName('project1.pdf')
            ->setThemeId($themeBudget->getId())
            ->setRank(0)
            ->setLinkedFileKey('project1File');


        $annex1Project2 = (new AnnexApi())
            ->setRank(0)
            ->setFileName('annex1_project2.pdf')
            ->setLinkedFileKey('Annex1Project2');

        $project2 = (new ProjectApi())
            ->setName('project2')
            ->setFileName('project2.pdf')
            ->setRank(1)
            ->setLinkedFileKey('project2File')
            ->setAnnexes([$annex1Project2]);

        $serializedProjects = $this->serializer->serialize([$project1, $project2], 'json');

        $this->client->request(Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingBureau->getId()}/projects",
            [
                'projects' => $serializedProjects
            ],
            [
                'project1File' => $project1File,
                'project2File' => $project2File,
                'Annex1Project2' => $annex1Project2File,
            ],
            [
                "HTTP_ACCEPT" => 'application/json',
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $projects = json_decode($response->getContent(), true);

        $this->assertcount(2, $projects);
        $this->assertSame(0, $projects[0]['rank']);
        $this->assertSame('project1', $projects[0]['name']);
        $this->assertSame('budget', $projects[0]['theme']['name']);
        $this->assertNotEmpty($projects[0]['file']);
        $this->assertSame('annex1_project2.pdf', $projects[01]['annexes'][0]['file']['name']);
    }

    public function testAddProjectsToSittingWithProjects()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);

        $this->client->request(Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects",
            [],
            [],
            [
                "HTTP_ACCEPT" => 'application/json',
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);
        $this->assertSame('Sitting already contain projects', $error['message']);
    }

    public function testDeleteProject()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $projectId = $this->getOneProjectBy(['name' => 'Project 1'])->getId();

        $this->client->request(Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects/{$projectId}",
            [],
            [],
            [
                "HTTP_ACCEPT" => 'application/json',
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(204);

        $deleted = $this->getOneProjectBy(['name' => 'Project 1']);
        $this->assertEmpty($deleted);

        $updatedRank = $this->getOneProjectBy(['name' => 'Project 2']);
        $this->assertSame(0, $updatedRank->getRank());
    }

}
