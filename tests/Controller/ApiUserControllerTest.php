<?php

namespace App\Tests\Controller;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiUserControllerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManagerInterface
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
            ApiUserFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->getConnection()->close();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/apikey');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Clés d\'api")');
        $this->assertCount(1, $title);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        $userApi = $this->getOneApiUserBy(['name' => 'connecteur api']);

        $this->client->request(Request::METHOD_DELETE, '/apikey/delete/' . $userApi->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("La clé d\'api a été supprimée")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneApiUserBy(['id' => $userApi->getId()]));
    }

    public function testAdd()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/apikey/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter une clé d\'api")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['api_user[name]'] = 'New api key';
        $form['api_user[token]'] = 'azerty';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La Clé d\'api a été ajoutée")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneApiUserBy(['name' => 'New api key']));
    }


    public function testEdit()
    {
        $this->loginAsAdminLibriciel();
        $userApi = $this->getOneApiUserBy(['name' => 'connecteur api']);
        $crawler = $this->client->request(Request::METHOD_GET, '/apikey/edit/' . $userApi->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier une clé d\'api")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['api_user[name]'] = 'updated name';
        $form['api_user[token]'] = 'updated token';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La clé d\'api a été modifiée")');
        $this->assertCount(1, $successMsg);

        $this->entityManager->refresh($userApi);

        $this->assertSame('updated name', $userApi->getName());
        $this->assertSame('updated token', $userApi->getToken());
    }



    public function testRefreshApiKey()
    {
        $this->client->request(Request::METHOD_GET, '/apikey/refresh');
        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $key = json_decode($response->getContent(), true);
        $this->assertNotEmpty($key['apiKey']);
    }
}
