<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\PartyFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class PartyApiControllerTest extends WebTestCase
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
            PartyFixtures::class,
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


    public function testGetAll()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/parties", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $parties = json_decode($response->getContent(), true);

        $this->assertCount(2, $parties);
    }

    public function testGetById()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertNotEmpty($party);
        $this->assertCount(1, $party['actors']);
    }

    public function testPost()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $data = [
            'name' => 'new party',
        ];

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/parties",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertNotEmpty($party['id']);
        $this->assertSame('new party', $party['name'] );

    }


    public function testUpdate()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $data = [
            'name' => 'updated',
        ];

        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $party = json_decode($response->getContent(), true);

        $this->assertSame('updated', $party['name'] );

    }

    public function testDelete()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);

        $this->client->request(Request::METHOD_DELETE, "/api/v2/structures/{$structure->getId()}/parties/{$party->getId()}",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()]);
        $this->assertResponseStatusCodeSame(204);

        $this->assertEmpty($this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]));
    }

}
