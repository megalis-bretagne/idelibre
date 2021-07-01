<?php

namespace App\Tests\Service\Theme;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemeManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\privateMethodTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ThemeManagerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;
    use privateMethodTrait;

    private ?KernelBrowser $client;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ThemeRepository
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

        $this->loadFixtures([
            ThemeFixtures::class,
            StructureFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testGenerateFullName()
    {
        /** @var Theme $theme */
        $themeBudget = $this->getOneEntityBy(Theme::class, ['name' => 'budget']);
        $themeManager = new ThemeManager($this->themeRepository, $this->entityManager);
        $generateFullNameFct = $this->getPrivateMethod(ThemeManager::class, 'generateFullName');
        $actual = $generateFullNameFct->invokeArgs($themeManager, [$themeBudget]);
        $this->assertEquals('Finance, budget', $actual);
    }

    public function testCreateThemesFromString()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        /** @var ThemeManager $themeManager */
        $themeManager = self::$container->get(ThemeManager::class);
        $themeManager->createThemesFromString("addedTheme", $structure);

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'addedTheme' ]);
        $this->assertCount(1, $addedThemes);

    }

    public function testCreateThemesFromStringAlreadyExists()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        /** @var ThemeManager $themeManager */
        $themeManager = self::$container->get(ThemeManager::class);
        $themeManager->createThemesFromString("Finance", $structure);
        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'Finance' ]);
        $this->assertCount(1, $addedThemes);
    }


    public function testCreateThemesFromStringTwice()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        /** @var ThemeManager $themeManager */
        $themeManager = self::$container->get(ThemeManager::class);
        $themeManager->createThemesFromString("addedTheme", $structure);
        $themeManager->createThemesFromString("addedTheme", $structure);

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $addedThemes = $themeRepository->findBy(['name' => 'addedTheme' ]);
        $this->assertCount(1, $addedThemes);

    }


}
