<?php

namespace App\Tests\Service\Csv;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\Service\Csv\CsvThemeManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;

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
    private ?object $csvThemeManager;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->csvThemeManager = self::getContainer()->get(CsvThemeManager::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            ThemeFixtures::class,
            StructureFixtures::class
        ]);
    }


    public function testImportThemes()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_success.csv', 'theme_success.csv');
        $this->assertNotEmpty($csvFile);

        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertEmpty($errors);

        $addTheme = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme);
    }



    public function testImportThemesWithSubThemes()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_withSubTheme.csv', 'theme_withSubTheme.csv');
        $this->assertNotEmpty($csvFile);

        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertEmpty($errors);

        $addedTheme2 = $this->getOneThemeBy(['name' => 'AddedTheme2', 'structure' => $structure]);
        $this->assertNotEmpty($addedTheme2);

        $addedTheme2SubTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme2_SubTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addedTheme2SubTheme1);
        $this->assertSame('AddedTheme2, AddedTheme2_SubTheme1', $addedTheme2SubTheme1->getFullName());
        $this->assertSame($addedTheme2->getId(), $addedTheme2SubTheme1->getParent()->getId());
    }


    public function testImportThemesNoName()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_noName.csv', 'theme_noName.csv');
        $this->assertNotEmpty($csvFile);

        /** @var ConstraintViolationList[] $errors */
        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertNotEmpty($errors);

        $this->assertSame('Cette valeur ne doit pas être vide.' ,$errors[0][0]->getMessage());

        $addTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme1);

        $addTheme3 = $this->getOneThemeBy(['name' => 'AddedTheme3', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme3);
    }

    public function testImportThemesTooLong()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_tooLong.csv', 'theme_tooLong.csv');
        $this->assertNotEmpty($csvFile);

        /** @var ConstraintViolationList[] $errors */
        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertNotEmpty($errors);
        $this->assertSame('Cette chaîne est trop longue. Elle doit avoir au maximum 255 caractères.' ,$errors[0][0]->getMessage());


        $addTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme1);

        $addTheme3 = $this->getOneThemeBy(['name' => 'AddedTheme3', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme3);
    }




}
