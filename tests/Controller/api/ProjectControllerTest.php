<?php

namespace App\Tests\Controller\api;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\ApiEntity\AnnexApi;
use App\Service\ApiEntity\ProjectApi;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\AnnexStory;
use App\Tests\Story\FileStory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private Serializer $serializer;
    private ProjectRepository $projectRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->projectRepository = self::getContainer()->get(ProjectRepository::class);
        $this->serializer = self::getContainer()->get('serializer');

        UserStory::load();
        AnnexStory::load();
        FileStory::load();
        ThemeStory::load();
        SittingStory::load();
        ProjectStory::load();
    }

    public function testEditAddProjects()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/convocation');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/project1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/annex1.pdf');

        $fileProject1 = new UploadedFile(TMP_TESTDIR . '/resources/project1.pdf', 'fichier.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile(TMP_TESTDIR . '/resources/project2.pdf', 'fichier.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(TMP_TESTDIR . '/resources/annex1.pdf', 'fichier.pdf', 'application/pdf');

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

        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/project1.txt');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', TMP_TESTDIR . '/resources/annex1.pdf');

        $fileProject1 = new UploadedFile(TMP_TESTDIR . '/resources/project1.txt', 'fichier.pdf', 'application/txt');
        $fileProject2 = new UploadedFile(TMP_TESTDIR . '/resources/project2.pdf', 'fichier.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(TMP_TESTDIR . '/resources/annex1.pdf', 'fichier.pdf', 'application/pdf');

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

        $sitting = SittingStory::sittingConseilLibriciel();
        $projectId = ProjectStory::project2()->getId();

        $project2 = new ProjectApi();
        $project2->setName('Project 2')
            ->setFileName('project2.pdf')
            ->setRank(0)
            ->setId($projectId);

        $this->loginAsAdminLibriciel();
        $serializedProjects = $this->serializer->serialize([$project2], 'json');

        $this->client->request(
            Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects]
        );

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
