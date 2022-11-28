<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\PartyStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PartyApiControllerTest extends WebTestCase
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
        StructureStory::libriciel();
        ApiUserStory::apiAdminLibriciel();
        PartyStory::majorite();
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/parties", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $parties = json_decode($response->getContent(), true);

        $this->assertCount(2, $parties);
    }

    public function testGetById()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $party = PartyStory::majorite();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertNotEmpty($party);
        $this->assertCount(1, $party['actors']);
    }

    public function testAdd()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $data = [
            'name' => 'new party',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/parties",
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertNotEmpty($party['id']);
        $this->assertSame('new party', $party['name']);
    }

    public function testAddNoName()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $data = [
            'name' => '',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/parties",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);

        $this->assertSame('Cette valeur ne doit pas être vide. ( name : "")', $error['message']);
    }

    public function testUpdate()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $party = PartyStory::majorite();

        $data = [
            'name' => 'updated',
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}",
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertSame('updated', $party['name']);
    }

    public function testDelete()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}",
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $apiUser->getToken()]
        );
        $this->assertResponseStatusCodeSame(204);

        $this->assertEmpty($this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]));
    }
}
