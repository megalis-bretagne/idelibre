<?php

namespace App\Tests\Controller\Csv;

use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class CsvThemeControllerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            UserFixtures::class,
            ThemeFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testImportTheme()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_success.csv', 'theme_success.csv');
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
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/theme_tooLong.csv', 'theme_tooLong.csv');
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
       dd($crawler);
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Certains thèmes n\'ont pas pu être importés")');
        $this->assertCount(1, $successMsg);


        $addedTheme1 = $this->getOneThemeBy(['name' => 'AddedTheme1']);
        $this->assertNotEmpty($addedTheme1);

    }

}
