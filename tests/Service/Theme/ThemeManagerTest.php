<?php

namespace App\Tests\Service\Theme;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\privateMethodTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ThemeManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;
    use privateMethodTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ThemeRepository $themeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->themeRepository = self::getContainer()->get(ThemeRepository::class);

        ThemeStory::load();
        StructureStory::load();
    }

    public function testGenerateFullName()
    {
        $themeBudget = ThemeStory::budgetTheme()->object();
        $themeManager = new ThemeManager($this->themeRepository, $this->entityManager);
        $generateFullNameFct = $this->getPrivateMethod(ThemeManager::class, 'generateFullName');
        $actual = $generateFullNameFct->invokeArgs($themeManager, [$themeBudget]);
        $this->assertEquals('Finance, budget', $actual);
    }

    public function testCreateThemesFromString()
    {
        $structure = StructureStory::libriciel();

        /** @var ThemeManager $themeManager */
        $themeManager = self::getContainer()->get(ThemeManager::class);
        $themeManager->createThemesFromString('addedTheme', $structure->object());

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'addedTheme']);
        $this->assertCount(1, $addedThemes);
    }

    public function testCreateThemesFromStringAlreadyExists()
    {
        $structure = StructureStory::libriciel();

        /** @var ThemeManager $themeManager */
        $themeManager = self::getContainer()->get(ThemeManager::class);
        $themeManager->createThemesFromString('Finance', $structure->object());
        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'Finance']);
        $this->assertCount(1, $addedThemes);
    }

    public function testCreateThemesFromStringTwice()
    {
        $structure = StructureStory::libriciel()->object();

        /** @var ThemeManager $themeManager */
        $themeManager = self::getContainer()->get(ThemeManager::class);
        $themeManager->createThemesFromString('addedTheme', $structure);
        $themeManager->createThemesFromString('addedTheme', $structure);

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'addedTheme']);
        $this->assertCount(1, $addedThemes);
    }

    public function testUpdate()
    {
        $structure = StructureStory::libriciel()->object();
        $theme = $this->getOneThemeBy(['name' => 'Finance', 'structure' => $structure]);

        /** @var ThemeManager $themeManager */
        $themeManager = self::getContainer()->get(ThemeManager::class);

        $theme->setName('updated name');
        $themeManager->update($theme);

        $this->entityManager->refresh($theme);
        $this->assertSame('updated name', $theme->getFullName());

        $subThemeBudget = $this->getOneThemeBy(['name' => 'budget', 'structure' => $structure]);

        $this->assertSame('updated name, budget', $subThemeBudget->getFullName());
    }
}
