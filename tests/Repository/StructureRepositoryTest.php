<?php

namespace App\Tests\Repository;

use App\Repository\StructureRepository;
use App\Tests\FindEntityTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class StructureRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;

    private ObjectManager $entityManager;
    private StructureRepository $structureRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->structureRepository = self::getContainer()->get(StructureRepository::class);

        self::ensureKernelShutdown();

        StructureStory::load();
        GroupStory::recia();
    }

    public function testFindByGroupQueryBuilder()
    {
        $groupRecia = GroupStory::recia();
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia->object());
        $this->assertCount(1, $qbStructure->getQuery()->getResult());
    }

    public function testFindByGroupQueryBuilderSearchExists()
    {
        $groupRecia = GroupStory::recia();
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia->object(), 'Mon');
        $this->assertCount(1, $qbStructure->getQuery()->getResult());
    }

    public function testFindByGroupQueryBuilderSearchNotExists()
    {
        $groupRecia = GroupStory::recia();
        $qbStructure = $this->structureRepository->findByGroupQueryBuilder($groupRecia->object(), 'notexists');
        $this->assertCount(0, $qbStructure->getQuery()->getResult());
    }
}
