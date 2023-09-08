<?php

namespace App\Tests\Controller\ApiV2;

use App\Security\Password\LegacyPassword;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\PartyStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserApiControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    public const REFERENCE = 'User_';

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private LegacyPassword $legacyPassword;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        UserStory::load();
        PartyStory::load();
        RoleStory::load();
        ApiUserStory::load();
        StructureStory::load();
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/users", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT-TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $users = json_decode($response->getContent(), true);

        $this->assertCount(6, $users);
    }

    public function testGetById()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT-TYPE' => 'application/json',
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
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $roleActor = RoleStory::actor();
        $party = PartyStory::majorite();

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
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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
        $user = json_decode($response->getContent(), true);

        $userBdd = $this->getOneUserBy(['id' => $user['id']]);

        $this->assertNotEmpty($userBdd->getId());
        $this->assertNotEmpty($userBdd->getParty());
        $this->assertNotEmpty($userBdd->getRole());
    }

    public function testPostWithPassword()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $roleActor = RoleStory::actor();
        $party = PartyStory::majorite();

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
            'password' => 'password',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $roleActor = RoleStory::actor();
        $party = PartyStory::majorite();

        $data = [
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'role' => $roleActor->getId(),
            'party' => $party->getId(),
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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

        $this->assertSame(
            'Cette valeur ne doit pas être vide. ( username : ""), Cette valeur ne doit pas être vide. ( email : "")',
            $error['message']
        );
    }

    public function testAddNoRole()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $data = [
            'username' => 'newUser',
            'email' => 'newUser@exemple.org',
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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
        $this->assertSame('Cette valeur ne doit pas être nulle. ( role : "")', $error['message']);
    }

    public function testAddForbiddenRole()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
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
            'phone' => '0607080919',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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
        $this->assertSame("You can't give role : {$roleSuperAdmin->getId()}", $error['message']);
    }

    public function testAddBadPartyId()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $roleActor = RoleStory::actor();
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
            'phone' => '0607080919',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/users",
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

        $this->assertSame("You can't use party : 216ddd2a-2ee8-4dd3-840d-efdd6f710ca0", $error['message']);
    }

    public function testUpdate()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();

        $data = [
            'username' => 'updatedUserName',
            'email' => 'updated@exemple.org',
            'firstName' => 'updatedFirstName',
            'lastName' => 'updatedLastName',
            //         'party' => null,
            'title' => 'Madame la vice présidente',
            'gender' => 1,
            'isActive' => true,
            'phone' => '0607080919',
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
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
        $user = json_decode($response->getContent(), true);

        $this->assertSame($user['username'], 'updatedUserName');
        $this->assertNotEmpty($user['party']);
        $this->assertNotEmpty($user['role']);
    }

    public function testUpdateWithPassword()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
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
            'phone' => '0607080919',
        ];

        $gg = $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
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
        $user = json_decode($response->getContent(), true);

        $this->assertSame($user['username'], 'updatedUserName');
        $this->assertNotEmpty($user['party']);
        $this->assertNotEmpty($user['role']);

        $passwordHasher = self::getContainer()->get('security.user_password_hasher');
        $this->assertTrue($passwordHasher->isPasswordValid($actorLs, $data['password']));
    }

    public function testUpdateParty()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();
        $partyOpposition = PartyStory::opposition();

        $data = [
            'party' => $partyOpposition->getId(),
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
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
        $user = json_decode($response->getContent(), true);

        $this->assertSame($user['party']['name'], 'Opposition');
    }

    public function testRemoveParty()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();

        $data = [
            'party' => null,
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
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
        $user = json_decode($response->getContent(), true);

        $this->assertEmpty($user['party']);
    }

    public function testUpdateRoleDoesNothing()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();
        $roleSecretary = RoleStory::secretary();

        $data = [
            'role' => $roleSecretary->getId(),
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
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
        $user = json_decode($response->getContent(), true);

        $this->assertSame('Actor', $user['role']['name']);
    }

    public function testDelete()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $actorLs = UserStory::actorLibriciel1();

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/users/{$actorLs->getId()}",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT-TYPE' => 'application/json',
            ]
        );

        $deleted = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $this->assertEmpty($deleted);
    }
}
