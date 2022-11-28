<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CheckApiControllerTest extends WebTestCase
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

        ApiUserStory::apiAdminLibriciel();
    }

    public function testPing()
    {
        $this->client->request(Request::METHOD_GET, '/api/v2/ping', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('success', $response['message']);
    }

    public function testMe()
    {
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/v2/me', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $me = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($me);
        $this->assertSame('connecteur api', $me['name']);
        $this->assertSame('Libriciel', $me['structure']['name']);
    }

    public function testMeWrongToken()
    {
        $badToken = '12345';
        $this->client->request(Request::METHOD_GET, '/api/v2/me', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X-AUTH-TOKEN' => $badToken,
        ]);

        $this->assertResponseStatusCodeSame(401);

        $error = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("Erreur d'authententification", $error['message']);
    }

    public function testMeNoToken()
    {
        $this->client->request(Request::METHOD_GET, '/api/v2/me', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);

        $error = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Full authentication is required to access this resource.', $error['message']);
    }
}
