<?php

namespace App\Tests\Repository;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\Entity\Annex;
use App\Repository\AnnexRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnnexRepositoryTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var AnnexRepository
     */
    private $annexRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->annexRepository = $this->entityManager->getRepository(Annex::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            ProjectFixtures::class,
            AnnexFixtures::class,
            FileFixtures::class,
            SittingFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testFindNotInListProjects()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $project1 = $this->getOneProjectBy(['name' => 'Project 1']);
        $annexId = $this->getOneAnnexBy(['project' => $project1, 'rank' => 0])->getId();
        $annexes = $this->annexRepository->findNotInListAnnexes([$annexId], $sitting);
        $this->assertCount(1, $annexes);
        $this->assertSame('Fichier annexe 2', $annexes[0]->getFile()->getName());
    }
}
