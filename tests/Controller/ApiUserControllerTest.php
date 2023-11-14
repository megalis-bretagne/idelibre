<?php

namespace App\Tests\Controller;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ApiUserControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        ApiUserStory::apiAdminLibriciel();
        UserStory::load();
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
        $item = $crawler->filter('html:contains("Ajout d\'une clé d\'api")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter la clé d\'api')->form();

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
        $userApi = ApiUserStory::apiAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/apikey/edit/' . $userApi->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification de la clé d\'api ")');
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

        $userApi->refresh();

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
