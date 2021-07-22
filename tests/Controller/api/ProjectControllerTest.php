<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Project;
use App\Repository\ProjectRepository;
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

class ProjectControllerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var object|Serializer|null
     */
    private $serializer;

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
            ProjectFixtures::class,
            UserFixtures::class,
            AnnexFixtures::class,
            FileFixtures::class,
            ThemeFixtures::class,
            SittingFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testEditAddProjects()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/convocation');

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1.pdf');

        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/project1.pdf', 'fichier.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'fichier.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(__DIR__ . '/../../resources/annex1.pdf', 'fichier.pdf', 'application/pdf');

        $annex = new AnnexApi();
        $annex->setLinkedFileKey('annex1')
            ->setRank(0);

        $project1 = new ProjectApi();
        $project1->setName('first Project')
            ->setFileName('project1.pdf')
            ->setRank(0)
            ->setLinkedFileKey('project1')
            ->setAnnexes([$annex]);

        $project2 = new ProjectApi();
        $project2->setName('second project')
            ->setFileName('project2.pdf')
            ->setRank(1)
            ->setLinkedFileKey('project2');

        $serializedProjects = $this->serializer->serialize([$project1, $project2], 'json');

        $this->client->request(
            Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects],
            [
                'project1' => $fileProject1,
                'project2' => $fileProject2,
                'annex1' => $fileAnnex1,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->entityManager->getRepository(Project::class);
        $project = $projectRepository->findOneBy(['name' => 'first Project']);
        $this->assertNotEmpty($project);
        $this->assertNotEmpty($project->getAnnexes());
    }


    public function testEditAddProjectsNoPdf()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.txt');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1.pdf');

        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/project1.txt', 'fichier.pdf', 'application/txt');
        $fileProject2 = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'fichier.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(__DIR__ . '/../../resources/annex1.pdf', 'fichier.pdf', 'application/pdf');

        $annex = new AnnexApi();
        $annex->setLinkedFileKey('annex1')
            ->setRank(0);

        $project1 = new ProjectApi();
        $project1->setName('first Project')
            ->setFileName('project1.txt')
            ->setRank(0)
            ->setLinkedFileKey('project1')
            ->setAnnexes([$annex]);

        $project2 = new ProjectApi();
        $project2->setName('second project')
            ->setFileName('project2.pdf')
            ->setRank(1)
            ->setLinkedFileKey('project2');

        $serializedProjects = $this->serializer->serialize([$project1, $project2], 'json');

        $this->client->request(
            Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects],
            [
                'project1' => $fileProject1,
                'project2' => $fileProject2,
                'annex1' => $fileAnnex1,
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertSame('Au moins un projet n\'est pas un pdf', $response->message);
    }



    public function testEditDeleteProjects()
    {
        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/convocation');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/fileProject2');

        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $projectId = $this->getOneProjectBy(['name' => 'Project 2'])->getId();

        $this->loginAsAdminLibriciel();

        $project2 = new ProjectApi();
        $project2->setName('Project 2')
            ->setFileName('project2.pdf')
            ->setRank(0)
            ->setId($projectId);

        $serializedProjects = $this->serializer->serialize([$project2], 'json');

        $this->client->request(
            Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects]
        );

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->entityManager->getRepository(Project::class);
        $this->assertNotEmpty($projectRepository->findOneBy(['name' => 'Project 2']));
        $this->assertEmpty($projectRepository->findOneBy(['name' => 'Project 1']));
    }

    public function testEditDeleteAnnex()
    {

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/convocation');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/fileProject1');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', '/tmp/fileProject2');

        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $project1 = $this->getOneProjectBy(['name' => 'Project 1']);
        $project2 = $this->getOneProjectBy(['name' => 'Project 2']);
        $annexId = $this->getOneAnnexBy(['project' => $project1, 'rank' => 0])->getId();

        $this->loginAsAdminLibriciel();

        $annex = new AnnexApi();
        $annex->setRank(0)
            ->setId($annexId);

        $project1Client = new ProjectApi();
        $project1Client->setName('Project 1')
            ->setFileName('project1.pdf')
            ->setRank(0)
            ->setId($project1->getId())
            ->setAnnexes([$annex]);

        $project2Client = new ProjectApi();
        $project2Client->setName('Project 2')
            ->setFileName('project2.pdf')
            ->setRank(1)
            ->setId($project2->getId());

        $serializedProjects = $this->serializer->serialize([$project1Client, $project2Client], 'json');

        $this->client->request(
            Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects]
        );

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->entityManager->getRepository(Project::class);
        $this->assertNotEmpty($projectRepository->findOneBy(['name' => 'Project 2']));
        $this->assertNotEmpty($projectRepository->findOneBy(['name' => 'Project 1']));

        $this->assertCount(1, $project1->getAnnexes());
    }

    public function testGetProjectsFromSitting()
    {
        $this->loginAsAdminLibriciel();
        $sitting = $this->getOneSittingBy(['name' => ['Conseil Libriciel']]);

        $this->client->request(Request::METHOD_GET, '/api/projects/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $projectsArray = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $projectsArray);
        $this->assertEquals('Project 1', $projectsArray[0]['name']);
        $this->assertNotEmpty($projectsArray[0]['themeId']);
        $this->assertNotEmpty($projectsArray[0]['reporterId']);
        $this->assertCount(2, $projectsArray[0]['annexes']);
        $this->assertNotEmpty($projectsArray[0]['annexes'][0]['fileName']);
    }
}
