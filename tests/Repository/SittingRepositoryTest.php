<?php

namespace App\Tests\Repository;

use App\Repository\SittingRepository;
use App\Tests\FindEntityTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SittingRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;

    private ObjectManager $entityManager;
    private SittingRepository $sittingRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->sittingRepository = self::getContainer()->get(SittingRepository::class);

        self::ensureKernelShutdown();

        StructureStory::libriciel();
        TypeStory::load();
        ConvocationStory::load();
    }

    public function testFindWithTypesByStructure()
    {
        $structureLs = StructureStory::libriciel();
        $typeConseil = TypeStory::typeConseilLibriciel();
        $typeBureau = TypeStory::typeBureauLibriciel();
        $typeNotUsed = TypeStory::testTypeLS();

        $this->assertCount(0, $this->sittingRepository->findWithTypesByStructure($structureLs->object(), [])->getQuery()->getResult());
        $this->assertCount(0, $this->sittingRepository->findWithTypesByStructure($structureLs->object(), [$typeNotUsed->getId()])->getQuery()->getResult());
        $this->assertCount(2, $this->sittingRepository->findWithTypesByStructure($structureLs->object(), [$typeConseil->getId()])->getQuery()->getResult());
        $this->assertCount(4, $this->sittingRepository->findWithTypesByStructure($structureLs->object(), [$typeConseil->getId(), $typeBureau->getId()])->getQuery()->getResult());
    }

    public function testFindByStructure()
    {
        $structureLs = StructureStory::libriciel();
        $this->assertCount(4, $this->sittingRepository->findByStructure($structureLs->object())->getQuery()->getResult());
    }

    public function testFindActiveFromStructure()
    {
        $structureLs = StructureStory::libriciel();
        $this->assertCount(1, $this->sittingRepository->findActiveFromStructure($structureLs->object())->getQuery()->getResult());
    }

    public function testFindSittingsAfter50Months()
    {
        $structureLs = StructureStory::libriciel();
        $sittings = $this->sittingRepository->findSittingsAfter(new \DateTime('-50 months'), $structureLs->object());
        $this->assertCount(4, $sittings);
    }

    public function testFindSittingsAfter3Months()
    {
        $structureLs = StructureStory::libriciel();
        $sittings = $this->sittingRepository->findSittingsAfter(new \DateTime('-3 months'), $structureLs->object());
        $this->assertCount(1, $sittings);
    }

    public function testFindSittingsBefore3Months()
    {
        $structureLs = StructureStory::libriciel();
        $sittings = $this->sittingRepository->findSittingsBefore(new \DateTime('-3 months'), $structureLs->object());
        $this->assertCount(3, $sittings);
    }

    public function testFindSittingsBefore50Months()
    {
        $structureLs = StructureStory::libriciel();
        $sittings = $this->sittingRepository->findSittingsBefore(new \DateTime('-50 months'), $structureLs->object());
        $this->assertCount(0, $sittings);
    }
}
