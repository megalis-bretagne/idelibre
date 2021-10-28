<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\StructureFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class CheckApiControllerTest extends WebTestCase
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
            ApiUserFixtures::class,
            StructureFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }


    public function testPing()
    {

        $this->client->request(Request::METHOD_GET, "/api/v2/ping", [], [], [
            "HTTP_ACCEPT" => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("success", $response['message']);
    }


    public function testMe()
    {
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $this->client->request(Request::METHOD_GET, "/api/v2/me", [], [], [
            "HTTP_ACCEPT" => 'application/json',
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);

        $this->assertResponseStatusCodeSame(200);

        $me = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($me);
        $this->assertSame('connecteur api', $me['name']);
        $this->assertSame('Libriciel', $me['structure']['name']);
    }

    public function testMeWrongToken()
    {
        $badToken = "12345";
        $this->client->request(Request::METHOD_GET, "/api/v2/me", [], [], [
            "HTTP_ACCEPT" => 'application/json',
            "HTTP_X-AUTH-TOKEN" => $badToken
        ]);

        $this->assertResponseStatusCodeSame(401);

        $error = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("Erreur d'authententification", $error['message']);
    }


    public function testMeNoToken()
    {
        $this->client->request(Request::METHOD_GET, "/api/v2/me", [], [], [
            "HTTP_ACCEPT" => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);

        $error = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("Full authentication is required to access this resource.", $error['message']);
    }


}
