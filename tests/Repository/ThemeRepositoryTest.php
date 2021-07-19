<?php

namespace App\Tests\Repository;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\Entity\Structure;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ThemeRepositoryTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var THemeRepository
     */
    private $themeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->themeRepository = $this->entityManager->getRepository(Theme::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            StructureFixtures::class,
            ThemeFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testFindChildrenFromStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);
        $this->assertCount(4, $this->themeRepository->findChildrenFromStructure($structure)->getQuery()->getResult());
    }
}
