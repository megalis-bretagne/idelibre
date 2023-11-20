<?php

namespace App\Tests\Repository;

use App\Repository\ThemeRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ThemeRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ThemeRepository $themeRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->themeRepository = self::getcontainer()->get(ThemeRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        StructureStory::libriciel();
        ThemeStory::load();
    }

    public function testFindChildrenFromStructure()
    {
        $structure = StructureStory::libriciel();
        $this->assertCount(4, $this->themeRepository->findChildrenFromStructure($structure->object())->getQuery()->getResult());
    }

    public function testGetNotChildrenNode()
    {
        $theme = ThemeStory::budgetTheme();
        $this->assertCount(3, $this->themeRepository->getNotChildrenNode($theme->object())->getQuery()->getResult());
    }
}
