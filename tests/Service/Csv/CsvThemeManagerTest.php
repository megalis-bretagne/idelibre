<?php

namespace App\Tests\Service\Csv;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\Entity\Theme;
use App\Service\Csv\CsvThemeManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvThemeManagerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /** @var CsvThemeManager  */
    private ?object $CsvThemeManager;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->CsvThemeManager = self::getContainer()->get(CsvThemeManager::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            ThemeFixtures::class,
            StructureFixtures::class
        ]);
    }


    public function testImportThemes()
    {
        dd('ok');
    }


}
