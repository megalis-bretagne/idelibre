<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TypeApiControllerTest extends WebTestCase
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

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $databaseTool->loadFixtures([
            UserFixtures::class,
            TypeFixtures::class,
            ApiUserFixtures::class,
            StructureFixtures::class
        ]);
    }

    public function testGetAll()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/types", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $types = json_decode($response->getContent(), true);

        $this->assertCount(3, $types);
    }


    public function testGetById()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type);
        $this->assertCount(2, $type['associatedUsers']);
    }


    public function testPost()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $data = [
            'name' => 'my new type',
            'isSms' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$actorLs->getId()]
        ];

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/types",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type['id']);
        $this->assertTrue($type['isSms']);
        $this->assertTrue($type['isComelus']);
        $this->assertSame(180, $type['reminder']['duration']);
        $this->assertCount(1, $type['associatedUsers']);
    }


    public function testPostAssociateNotSameStructureUsers()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $userMtp = $this->getOneUserBy(['username' => 'user@montpellier']);

        $data = [
            'name' => 'my new type',
            'isSms' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$userMtp->getId()]
        ];


        $client = self::getContainer()->get(HttpClientInterface::class);

        $res = $client->request(Request::METHOD_POST, "https://localhost/api/v2/structures/{$structure->getId()}/types", [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        dd($res);

        //$request = new \Nyholm\Psr7\Request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/types",
       // ['Content-Type' => 'application/json'], json_encode($data))  ;

        //dd($response);

        //$request = $client->createRequest(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/types");
        //$request->withBody(json_encode($data))

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/types",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'HTTP_Accept' => 'application/json',
                'HTTP_Content-Type' => 'application/json'

            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(403);

        $response = $this->client->getResponse();

        dd($response->getContent());
        $error = json_decode($response->getContent(), true);



    }


    public function testUpdate()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);


        $data = [
            'name' => 'updated name',
            'isSms' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$actorLs->getId()]
        ];

        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type['id']);
        $this->assertSame($type['name'], 'updated name');
        $this->assertTrue($type['isSms']);
        $this->assertTrue($type['isComelus']);
        $this->assertSame(180, $type['reminder']['duration']);
        $this->assertCount(1, $type['associatedUsers']);
    }

    public function testDelete()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);

        $this->client->request(Request::METHOD_DELETE, "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()]);
        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']));
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

}
