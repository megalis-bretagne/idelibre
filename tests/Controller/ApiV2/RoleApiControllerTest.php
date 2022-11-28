<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RoleApiControllerTest extends WebTestCase
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
    }

    public function testGetAll()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/roles", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $roles = json_decode($response->getContent(), true);

        $this->assertCount(5, $roles);
    }
}
