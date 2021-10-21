<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\PartyFixtures;
use App\DataFixtures\RoleFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserApiControllerTest extends WebTestCase
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
            RoleFixtures::class,
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

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/users", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $users = json_decode($response->getContent(), true);

        $this->assertCount(12, $users);
    }

    public function testGetById()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        $this->assertNotEmpty($user);
        $this->assertNotEmpty($user['role']);
        $this->assertNotEmpty($user['party']);
    }


    public function testPost()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $roleActor = $this->getOneRoleBy(['name' => 'Actor']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);


        $data = [
            'username' => 'newUser',
            'email' => 'newUser@exemple.org',
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'role' => $roleActor->getId(),
            'party' => $party->getId(),
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];


        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/users",
            [],
            [],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        dd($user);

        $userBdd = $this->getOneUserBy(['id' => $user['id']]);
        dd($userBdd);

        $this->assertNotEmpty($party['id']);
        $this->assertSame('new party', $party['name']);

    }



}
