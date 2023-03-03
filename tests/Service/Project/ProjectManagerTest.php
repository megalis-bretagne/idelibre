<?php

namespace App\Tests\Service\Project;

use App\Service\Project\ProjectManager;
use App\Tests\Factory\AnnexFactory;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ProjectManager $projectManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->projectManager = self::getContainer()->get(ProjectManager::class);

        self::ensureKernelShutdown();
    }

    public function testGetProjectFromSitting()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $file = FileFactory::createOne([
            'name' => 'fichier',
            'path' => '/tmp/file.pdf',
            'size' => 100,
        ]);
        ProjectFactory::createMany(1, [
            'sitting' => $sitting,
            'rank' => 3,
            'file' => $file,
        ]);

        $projects = $this->projectManager->getProjectsFromSitting($sitting);

        $this->assertCount(1, $projects);
    }

    public function testGetApiProjectsFromProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $file1 = FileFactory::createOne([
            'name' => 'fichier',
            'path' => '/tmp/file.pdf',
            'size' => 100,
        ]);
        $file2 = FileFactory::createOne([
            'name' => 'fichier',
            'path' => '/tmp/file.pdf',
            'size' => 100,
        ]);
        $project1 = ProjectFactory::createOne([
            'sitting' => $sitting,
            'rank' => 3,
            'file' => $file1,
        ]);
        $project2 = ProjectFactory::createOne([
            'sitting' => $sitting,
            'rank' => 3,
            'file' => $file2,
        ]);

        $all_projects = [$project1, $project2];

        $projects = $this->projectManager->getApiProjectsFromProjects($all_projects);

        $this->assertCount(2, $projects);
    }

    public function testGetApiAnnexesFromAnnexes()
    {
        $project = ProjectStory::project1();
        $file1 = FileFactory::createOne([
            'name' => 'fichier',
            'path' => '/tmp/file.pdf',
            'size' => 100,
        ]);
        $file2 = FileFactory::createOne([
            'name' => 'fichier',
            'path' => '/tmp/file.pdf',
            'size' => 100,
        ]);
        $annexe1 = AnnexFactory::createOne([
            'project' => $project,
            'rank' => 3,
            'file' => $file1,
        ]);
        $annexe2 = AnnexFactory::createOne([
            'project' => $project,
            'rank' => 3,
            'file' => $file2,
        ]);

        $all_annexes = [$annexe1, $annexe2];

        $annexes = $this->projectManager->getApiAnnexesFromAnnexes($all_annexes);

        $this->assertCount(2, $annexes);
    }
}
