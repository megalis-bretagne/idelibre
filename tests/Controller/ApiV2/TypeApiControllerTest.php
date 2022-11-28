<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TypeApiControllerTest extends WebTestCase
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

        UserStory::actorLibriciel1();
        TypeStory::typeConseilLibriciel();
        StructureStory::libriciel();
        ApiUserStory::apiAdminLibriciel();
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/types", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $types = json_decode($response->getContent(), true);

        $this->assertCount(3, $types);
    }

    public function testGetById()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $type = TypeStory::typeConseilLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type);
        $this->assertCount(2, $type['associatedUsers']);
    }

    public function testPost()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();

        $data = [
            'name' => 'my new type',
            'isSms' => true,
            'isSmsEmployees' => true,
            'isSmsGuests' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$actorLs->getId()],
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/types",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type['id']);
        $this->assertTrue($type['isSms']);
        $this->assertTrue($type['isSmsEmployees']);
        $this->assertTrue($type['isSmsGuests']);
        $this->assertTrue($type['isComelus']);
        $this->assertSame(180, $type['reminder']['duration']);
        $this->assertCount(1, $type['associatedUsers']);
    }

    public function testPostAssociateNotSameStructureUsers()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $userMtp = $this->getOneUserBy(['username' => 'user@montpellier']);

        $data = [
            'name' => 'my new type',
            'isSms' => true,
            'isSmsEmployees' => true,
            'isSmsGuests' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$userMtp->getId()],
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/types",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(403);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);

        $this->assertSame("some users does not belong to your structure : {$userMtp->getId()}", $error['message']);
    }

    public function testUpdate()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();
        $type = TypeStory::typeConseilLibriciel()

;
        $data = [
            'name' => 'updated name',
            'isSms' => true,
            'isSmsEmployees' => true,
            'isSmsGuests' => true,
            'isComelus' => true,
            'reminder' => ['duration' => 180, 'isActive' => true],
            'associatedUsers' => [$actorLs->getId()],
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $type = json_decode($response->getContent(), true);

        $this->assertNotEmpty($type['id']);
        $this->assertSame($type['name'], 'updated name');
        $this->assertTrue($type['isSms']);
        $this->assertTrue($type['isSmsEmployees']);
        $this->assertTrue($type['isSmsGuests']);
        $this->assertTrue($type['isComelus']);
        $this->assertSame(180, $type['reminder']['duration']);
        $this->assertCount(1, $type['associatedUsers']);
    }

    public function testDelete()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $type = TypeStory::typeConseilLibriciel();

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/types/{$type->getId()}",
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $apiUser->getToken()]
        );
        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']));
    }
}
