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


class RoleApiControllerTest extends WebTestCase
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

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/roles", [], [], [
            "HTTP_X-AUTH-TOKEN" => $apiUser->getToken()
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $roles = json_decode($response->getContent(), true);

        $this->assertCount(5, $roles);
    }



}
