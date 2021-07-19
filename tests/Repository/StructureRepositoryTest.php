<?php

namespace App\Tests\Repository;

use App\DataFixtures\GroupFixtures;
use App\DataFixtures\StructureFixtures;
use App\Entity\Structure;
use App\Repository\StructureRepository;
use App\Tests\FindEntityTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StructureRepositoryTest extends WebTestCase
{
    use FindEntityTrait;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    private StructureRepository $structureRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->structureRepository = $this->entityManager->getRepository(Structure::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            StructureFixtures::class,
            GroupFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testFindByGroupQueryBuilder()
    {
        $groupRecia = $this->getOneGroupBy(['name' => 'Recia']);
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia);
        $this->assertCount(1, $qbStructure->getQuery()->getResult());
    }

    public function testFindByGroupQueryBuilderSearchExists()
    {
        $groupRecia = $this->getOneGroupBy(['name' => 'Recia']);
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia, 'Mon');
        $this->assertCount(1, $qbStructure->getQuery()->getResult());
    }

    public function testFindByGroupQueryBuilderSearchNotExists()
    {
        $groupRecia = $this->getOneGroupBy(['name' => 'Recia']);
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia, 'notexists');
        $this->assertCount(0, $qbStructure->getQuery()->getResult());
    }
}
