<?php

namespace App\Tests\Repository;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\RoleFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Sitting;
use App\Repository\SittingRepository;
use App\Tests\FindEntityTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SittingRepositoryTest extends WebTestCase
{
    use FindEntityTrait;

    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var SittingRepository
     */
    private $sittingRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->sittingRepository = $this->entityManager->getRepository(Sitting::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            StructureFixtures::class,
            UserFixtures::class,
            TypeFixtures::class,
            SittingFixtures::class,
            RoleFixtures::class,
            ConvocationFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testFindWithTypesByStructure()
    {
        $structureLs = $this->getOneStructureBy(['name' => 'Libriciel']);
        $typeConseil = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);
        $typeBureau = $this->getOneTypeBy(['name' => 'Bureau Communautaire Libriciel']);
        $typeNotUsed = $this->getOneTypeBy(['name' => 'unUsedType']);

        $this->assertCount(0, $this->sittingRepository->findWithTypesByStructure($structureLs, [])->getQuery()->getResult());
        $this->assertCount(0, $this->sittingRepository->findWithTypesByStructure($structureLs, [$typeNotUsed])->getQuery()->getResult());
        $this->assertCount(1, $this->sittingRepository->findWithTypesByStructure($structureLs, [$typeConseil])->getQuery()->getResult());
        $this->assertCount(2, $this->sittingRepository->findWithTypesByStructure($structureLs, [$typeConseil, $typeBureau])->getQuery()->getResult());
    }

    public function testFindByStructure()
    {
        $structureLs = $this->getOneStructureBy(['name' => 'Libriciel']);

        $this->assertCount(2, $this->sittingRepository->findByStructure($structureLs)->getQuery()->getResult());
    }

    public function testFindActiveFromStructure()
    {
        $structureLs = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->assertCount(1, $this->sittingRepository->findActiveFromStructure($structureLs)->getQuery()->getResult());
    }
}
