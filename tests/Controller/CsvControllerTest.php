<?php

namespace App\Tests\Controller;

use App\DataFixtures\RoleFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Type;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class CsvControllerTest extends WebTestCase
{
    use FixturesTrait;
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

        $this->loadFixtures([
            UserFixtures::class,
            RoleFixtures::class,
            TypeFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testImportUsers()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../resources/user_success.csv', 'user_success.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Fichier csv importé avec succès")');
        $this->assertCount(1, $successMsg);

        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 't.martin@libriciel']);
        $this->assertNotEmpty($user);
        $this->assertCount(4, $user->getAssociatedTypes()->toArray());
        $this->assertNotEmpty($this->getOneEntityBy(Type::class, ['name' => 'New type']));
    }

    public function testImportUsersMissingEmail()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../resources/user_email_missing.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['username' => 't.martin@libriciel']));
        $this->assertEmpty($this->getOneEntityBy(Type::class, ['name' => 'New type']));

        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'e.dupont@libriciel']));
    }

    public function testCsvErrorWithNoError()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/csv/errors');
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Utilisateurs")');
        $this->assertCount(1, $title);
    }
}
