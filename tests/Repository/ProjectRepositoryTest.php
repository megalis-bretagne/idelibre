<?php

namespace App\Tests\Repository;

use App\Repository\ProjectRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ProjectRepository $projectRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->projectRepository = self::getContainer()->get(ProjectRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        SittingStory::sittingConseilLibriciel();
        ProjectStory::project2();
    }

    public function testGetProjectsWithAssociatedEntities()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $projects = $this->projectRepository->getProjectsWithAssociatedEntities($sitting->object());
        $this->assertCount(3, $projects);
    }

    public function testFindNotInListProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $project = ProjectStory::project2();
        $projects = $this->projectRepository->findNotInListProjects([$project->getId()], $sitting->object());
        $this->assertCount(2, $projects);
    }
}
