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


    public function testPostWithPassword()
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
            'phone' => '0607080919',
            'password' => 'password'
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

        $passwordHasher = self::getContainer()->get('security.user_password_hasher');
        $this->assertTrue($passwordHasher->isPasswordValid($userBdd, $data['password']));
    }


    public function testAddNoUsernameAndEmail()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $roleActor = $this->getOneRoleBy(['name' => 'Actor']);
        $party = $this->getOnePartyBy(['name' => 'Majorité', 'structure' => $structure]);


        $data = [
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
        $this->assertResponseStatusCodeSame(400);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);

        $this->assertSame(
            "Cette valeur ne doit pas être vide. ( username : \"\"), Cette valeur ne doit pas être vide. ( email : \"\")",
            $error["message"]
        );
    }

    public function testAddNoRole()
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
        $this->assertSame('Cette valeur ne doit pas être nulle. ( role : "")', $error['message']);

    }

    public function testAddForbiddenRole()
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


    public function testAddBadPartyId()
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
        $this->assertResponseStatusCodeSame(403);

        $response = $this->client->getResponse();
        $error = json_decode($response->getContent(), true);

        $this->assertSame("You can't use party : 216ddd2a-2ee8-4dd3-840d-efdd6f710ca0", $error['message']);

    }


    public function testUpdate()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $data = [
            'username' => 'updatedUserName',
            'email' => 'updated@exemple.org',
            'firstName' => 'updatedFirstName',
            'lastName' => 'updatedLastName',
            //         'party' => null,
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];


        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);


        $this->assertSame($user['username'], 'updatedUserName');
        $this->assertNotEmpty($user['party']);
        $this->assertNotEmpty($user['role']);
    }


    public function testUpdateWithPassword()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $data = [
            'username' => 'updatedUserName',
            'email' => 'updated@exemple.org',
            'firstName' => 'updatedFirstName',
            'lastName' => 'updatedLastName',
            'password' => 'passwordChange',
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919'
        ];


        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);


        $this->assertSame($user['username'], 'updatedUserName');
        $this->assertNotEmpty($user['party']);
        $this->assertNotEmpty($user['role']);

        $this->entityManager->refresh($actorLs);
        $passwordHasher = self::getContainer()->get('security.user_password_hasher');
        $this->assertTrue($passwordHasher->isPasswordValid($actorLs, $data['password']));
    }


    public function testUpdateParty()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $partyOpposition = $this->getOnePartyBy(['name' => 'Opposition', 'structure' => $structure]);

        $data = [
            'party' => $partyOpposition->getId(),
        ];

        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        $this->assertSame($user['party']['name'], 'Opposition');

    }


    public function testRemoveParty()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $data = [
            'party' => null,
        ];

        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        $this->assertEmpty($user['party']);

    }


    public function testUpdateRoleDoesNothing()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $roleSecretary = $this->getOneRoleBy(['name' => 'Secretary']);


        $data = [
            'role' => $roleSecretary->getId()
        ];

        $this->client->request(Request::METHOD_PUT, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);

        $this->assertSame('Actor', $user['role']['name']);
    }


    public function testDelete()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $this->client->request(Request::METHOD_DELETE, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}", [], [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                'CONTENT-TYPE' => 'application/json'
            ]
        );

        $deleted = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $this->assertEmpty($deleted);
    }

}



