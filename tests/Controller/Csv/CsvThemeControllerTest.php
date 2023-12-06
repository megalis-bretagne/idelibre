<?php

namespace App\Tests\Controller\Csv;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CsvThemeControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        UserStory::load();
        ThemeStory::load();
    }

    public function testImportTheme()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_success.csv', 'theme_success.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importTheme');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des thèmes via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Fichier csv importé avec succès")');
        $this->assertCount(1, $successMsg);

        $addedTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1']);
        $this->assertNotEmpty($addedTheme1);
    }

    public function testImportCsvError()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/theme_tooLong.csv', 'theme_tooLong.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importTheme');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des thèmes via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Certains thèmes n\'ont pas pu être importés")');
        $this->assertCount(1, $successMsg);

        $addedTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1']);
        $this->assertNotEmpty($addedTheme1);
    }
}
