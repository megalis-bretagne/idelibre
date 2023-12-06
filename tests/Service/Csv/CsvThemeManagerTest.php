<?php

namespace App\Tests\Service\Csv;

use App\Service\Csv\CsvThemeManager;
use App\Tests\Factory\ThemeFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CsvThemeManagerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;

    private CsvThemeManager $csvThemeManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->csvThemeManager = self::getContainer()->get(CsvThemeManager::class);
    }

    public function testImportThemes()
    {
        $libricielProxy = StructureStory::libriciel();
        $rootTheme = ThemeFactory::createOne(['name' => 'ROOT', 'structure' => $libricielProxy]);
        $structure = $libricielProxy->object();

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_success.csv', 'theme_success.csv');
        $this->assertNotEmpty($csvFile);

        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertEmpty($errors);

        $addTheme = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme);
    }

    public function testImportThemesWithSubThemes()
    {
        $libricielProxy = StructureStory::libriciel();
        $rootTheme = ThemeFactory::createOne(['name' => 'ROOT', 'structure' => $libricielProxy]);
        $structure = $libricielProxy->object();

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_withSubTheme.csv', 'theme_withSubTheme.csv');
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
        $libricielProxy = StructureStory::libriciel();
        $rootTheme = ThemeFactory::createOne(['name' => 'ROOT', 'structure' => $libricielProxy]);
        $structure = $libricielProxy->object();

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_noName.csv', 'theme_noName.csv');
        $this->assertNotEmpty($csvFile);

        /** @var ConstraintViolationList[] $errors */
        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertNotEmpty($errors);

        $this->assertSame('Cette valeur ne doit pas être vide.', $errors[0][0]->getMessage());

        $addTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme1);

        $addTheme3 = $this->getOneThemeBy(['name' => 'AddedTheme3', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme3);
    }

    public function testImportThemesTooLong()
    {
        $libricielProxy = StructureStory::libriciel();
        $rootTheme = ThemeFactory::createOne(['name' => 'ROOT', 'structure' => $libricielProxy]);
        $structure = $libricielProxy->object();

        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_tooLong.csv', 'theme_tooLong.csv');
        $this->assertNotEmpty($csvFile);

        /** @var ConstraintViolationList[] $errors */
        $errors = $this->csvThemeManager->importThemes($csvFile, $structure);

        $this->assertNotEmpty($errors);
        $this->assertSame('Cette chaîne est trop longue. Elle doit avoir au maximum 255 caractères.', $errors[0][0]->getMessage());

        $addTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme1);

        $addTheme3 = $this->getOneThemeBy(['name' => 'AddedTheme3', 'structure' => $structure]);
        $this->assertNotEmpty($addTheme3);
    }
}
