<?php

namespace App\Tests\Controller\ApiV2;

use App\Service\ApiEntity\AnnexApi;
use App\Service\ApiEntity\ProjectApi;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\TypeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SittingApiControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $this->serializer = self::getContainer()->get('serializer');

        ConvocationStory::load();
        ProjectStory::project1();
        StructureStory::libriciel();
        ApiUserStory::apiAdminLibriciel();
        SittingStory::load();
        ThemeStory::budgetTheme();
        TypeStory::typeConseilLibriciel();
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/sittings", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();
        $sittings = json_decode($response->getContent(), true);

        $this->assertCount(4, $sittings);
    }

    public function testGetOne()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);

        $this->assertNotEmpty($sitting);
        $this->assertSame($sittingConseil->getId(), $sitting['id']);
    }

    public function testGetAllConvocations()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();

        $this->client->request(
            Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/convocations",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $convocations = json_decode($response->getContent(), true);

        $this->assertCount(2, $convocations);
    }

    public function testGetAllProjects()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();

        $this->client->request(
            Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $projects = json_decode($response->getContent(), true);

        $this->assertCount(2, $projects);
    }

    public function testAddSitting()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $type = TypeStory::typeConseilLibriciel();
        $convocation = ConvocationStory::load();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/invitation.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation.pdf');

        $invitationFile = new UploadedFile(__DIR__ . '/../../resources/invitation.pdf', 'invitation.pdf', 'application/pdf');
        $convocationFile = new UploadedFile(__DIR__ . '/../../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings",
            [
                'date' => '2020-10-22 11:00:00',
                'type' => $type->getId(),
                'place' => 'salle du conseil',
            ],
            [
                'invitationFile' => $invitationFile,
                'convocationFile' => $convocationFile,
            ],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);

        $this->assertNotEmpty($sitting['id']);
        $this->assertSame($sitting['name'], 'Conseil Communautaire Libriciel');
        $this->assertNotEmpty($sitting['convocationFile']);
        $this->assertNotEmpty($sitting['invitationFile']);
    }

    public function testAddSittingNoConvocationFile()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $type = TypeStory::typeConseilLibriciel();

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings",
            [
                'date' => '2020-10-22 11:00:00',
                'type' => $type->getId(),
                'place' => 'salle du conseil',
            ],
            [
            ],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $decodedContent = json_decode($response->getContent());
        $this->assertSame('File with key convocationFile is required', $decodedContent->message);
    }

    public function testUpdateSitting()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/invitation_updated.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation_updated.pdf');

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/fileProject1');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/fileProject2');

        $invitationFile = new UploadedFile(__DIR__ . '/../../resources/invitation_updated.pdf', 'invitation_updated.pdf', 'application/pdf');
        $convocationFile = new UploadedFile(__DIR__ . '/../../resources/convocation_updated.pdf', 'convocation_updated.pdf', 'application/pdf');

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}",
            [
                'date' => '2020-10-22 17:30:00',
                'place' => 'salle de la mairie',
            ],
            [
                'invitationFile' => $invitationFile,
                'convocationFile' => $convocationFile,
            ],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $response = $this->client->getResponse();
        $sitting = json_decode($response->getContent(), true);
        $this->assertSame($sittingConseil->getId(), $sitting['id']);
        $this->assertSame('Conseil Libriciel', $sitting['name']);
        $this->assertSame('convocation_updated.pdf', $sitting['convocationFile']['name']);
        $this->assertSame('invitation_updated.pdf', $sitting['invitationFile']['name']);
        $this->assertNotEmpty($sitting['invitationFile']);
    }

    public function testAddProjectsToSitting()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingBureau = SittingStory::sittingBureauLibriciel();
        $themeBudget = ThemeStory::budgetTheme();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1_project2.pdf');

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/convocation');

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

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingBureau->getId()}/projects",
            [
                'projects' => $serializedProjects,
            ],
            [
                'project1File' => $project1File,
                'project2File' => $project2File,
                'Annex1Project2' => $annex1Project2File,
            ],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
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

    public function testAddProjectsToSittingMalformedJson()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingBureau = SittingStory::sittingBureauLibriciel();

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingBureau->getId()}/projects",
            [
                'projects' => 'malformedJson',
            ],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertSame('malformed json', $content->message);
    }

    public function testAddProjectsToSittingWithProjects()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects",
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);
        $this->assertSame('Sitting already contain projects', $error['message']);
    }

    public function testDeleteProject()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sittingConseil = SittingStory::sittingConseilLibriciel();
        $projectId = ProjectStory::project1()->getId();

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/projects/{$projectId}",
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            ],
        );

        $this->assertResponseStatusCodeSame(204);

        $deleted = $this->getOneProjectBy(['name' => 'Project 1']);
        $this->assertEmpty($deleted);

        $updatedRank = $this->getOneProjectBy(['name' => 'Project 2']);
        $this->assertSame(0, $updatedRank->getRank());
    }
}
