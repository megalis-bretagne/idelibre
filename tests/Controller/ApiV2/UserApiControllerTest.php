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
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            'CONTENT-TYPE' => 'application/json'
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
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
            'CONTENT-TYPE' => 'application/json'
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
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);

        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        $userBdd = $this->getOneUserBy(['id' => $user['id']]);

        $this->assertNotEmpty($userBdd->getId());
        $this->assertNotEmpty($userBdd->getParty());
        $this->assertNotEmpty($userBdd->getRole());
    }



    public function testPostNoRole()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);

        $data = [
            'username' => 'newUser',
            'email' => 'newUser@exemple.org',
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/users",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);
        $this->assertSame('Role must be set', $error['message'] );

    }

    public function testPostForbiddenRole()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $roleSuperAdmin = $this->getOneRoleBy(['name' => 'SuperAdmin']);

        $data = [
            'username' => 'newUser',
            'email' => 'newUser@exemple.org',
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'role' => $roleSuperAdmin->getId(),
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/users",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(403);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);
        $this->assertSame("You can't give role : {$roleSuperAdmin->getId()}", $error['message']);

    }


    public function testPostBadPartyId()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $roleActor = $this->getOneRoleBy(['name' => 'Actor']);
        $fakePartyId = '216ddd2a-2ee8-4dd3-840d-efdd6f710ca0';


        $data = [
            'username' => 'newUser',
            'email' => 'newUser@exemple.org',
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'role' => $roleActor->getId(),
            'party' => $fakePartyId,
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];

        $this->client->request(Request::METHOD_POST, "/api/v2/structures/{$structure->getId()}/users",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);
        $this->assertSame("You can't use party : 216ddd2a-2ee8-4dd3-840d-efdd6f710ca0", $error['message']);

    }
}



