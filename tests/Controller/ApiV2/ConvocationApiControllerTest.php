<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TimestampFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ConvocationApiControllerTest extends WebTestCase
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
            ApiUserFixtures::class,
            SittingFixtures::class,
            StructureFixtures::class,
            ConvocationFixtures::class,
            TimestampFixtures::class,
            FileFixtures::class
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
        $sittingConseil = $this->getOneSittingBy(['name' => 'Conseil Libriciel', 'structure' => $structure]);

        $this->client->request(Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sittingConseil->getId()}/convocations",
            [], [],
            [
                "HTTP_X-AUTH-TOKEN" => $apiUser->getToken(),
                "CONTENT_TYPE" => 'application/json'
            ]);

        $response = $this->client->getResponse();
        $sittings = json_decode($response->getContent(), true);

        dd($sittings);

        $this->assertCount(2, $sittings);
    }


}
