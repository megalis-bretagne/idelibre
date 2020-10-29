<?php

namespace App\Tests\Repository;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\GroupFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\RoleFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Group;
use App\Entity\Project;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectRepositoryTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var ProjectRepository
     */
    private $projectRepository;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->projectRepository = $this->entityManager->getRepository(Project::class);

        $this->loadFixtures([
            ProjectFixtures::class,
            AnnexFixtures::class,
            ThemeFixtures::class,
            FileFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testgetProjectsWithAssociatedEntities()
    {
        $sitting = $this->getOneSittingBy(['name' => ['Conseil Libriciel']]);

        $projects = $this->projectRepository->getProjectsWithAssociatedEntities($sitting);
        $this->assertCount(2, $projects);
    }

}
