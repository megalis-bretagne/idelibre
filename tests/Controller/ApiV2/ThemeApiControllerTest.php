<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ThemeApiControllerTest extends WebTestCase
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

        ThemeStory::budgetTheme();
        StructureStory::libriciel();
        ApiUserStory::apiAdminLibriciel();
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/themes", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $themes = json_decode($response->getContent(), true);

        $this->assertCount(4, $themes);
    }

    public function testGetById()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $theme = ThemeStory::budgetTheme();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/themes/{$theme->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $theme = json_decode($response->getContent(), true);

        $this->assertNotEmpty($theme['id']);
    }

    public function testAddMainTheme()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $data = [
            'name' => 'new theme',
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/themes",
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
        $theme = json_decode($response->getContent(), true);

        $this->assertNotEmpty($theme['id']);

        $inDbTheme = $this->getOneThemeBy(['id' => $theme['id']]);
        $this->assertNotEmpty($inDbTheme);
    }

    public function testAddSubTheme()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $theme = ThemeStory::budgetTheme();

        $data = [
            'name' => 'new theme with parent',
            'parent' => $theme->getId(),
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/themes",
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
        $theme = json_decode($response->getContent(), true);

        $this->assertNotEmpty($theme['id']);

        $inDbTheme = $this->getOneThemeBy(['id' => $theme['id']]);
        $this->assertNotEmpty($inDbTheme);
        $this->assertSame('Finance, budget', $inDbTheme->getParent()->getFullName());
        $this->assertSame('Finance, budget, new theme with parent', $inDbTheme->getFullName());
    }

    public function testAddForbiddenSubTheme()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $themeId = '1199fb00-b1bf-4243-98d6-50fcce089ee1';

        $data = [
            'name' => 'new theme with parent',
            'parent' => $themeId,
        ];

        $this->client->request(
            Request::METHOD_POST,
            "/api/v2/structures/{$structure->getId()}/themes",
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

        $this->assertSame('You cannot use this parent : 1199fb00-b1bf-4243-98d6-50fcce089ee1', $error['message']);
    }

    //# A VERIFIER ##
    public function testUpdateTheme()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $theme = ThemeStory::budgetTheme();

        $data = [
            'name' => 'new name',
        ];

        $this->client->request(
            Request::METHOD_PUT,
            "/api/v2/structures/{$structure->getId()}/themes/{$theme->getId()}",
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
        $theme = json_decode($response->getContent(), true);

        $this->assertNotEmpty($theme['id']);

        $inDbTheme = $this->getOneThemeBy(['id' => $theme['id']]);

        $this->assertNotEmpty($inDbTheme);
        $this->assertSame('new name', $inDbTheme->getName());
        $this->assertSame('Finance, new name', $inDbTheme->getFullName());
    }

    public function testDeleteTheme()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $theme = ThemeStory::budgetTheme();
        $themeId = $theme->getId();

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/v2/structures/{$structure->getId()}/themes/{$theme->getId()}",
            [],
            [],
            [
                'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertResponseStatusCodeSame(204);

        $inDbTheme = $this->getOneThemeBy(['id' => $themeId]);

        $this->assertEmpty($inDbTheme);
    }
}
