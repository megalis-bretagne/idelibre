<?php

namespace App\Tests\Controller\ApiV2;

use App\DataFixtures\ApiUserFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\PartyFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TimestampFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;


class TokenApiControllerTest extends WebTestCase
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
            SittingFixtures::class,
            ConvocationFixtures::class,
            TimestampFixtures::class,
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


    public function testGetSittingZipTokens()
    {

        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $apiUser = $this->getOneApiUserBy(['token' => '1234']);
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $bag = self::getContainer()->get('parameter_bag');
        $year = $sitting->getDate()->format('Y');
        $tokenPath = "{$bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}";

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../../resources/timestampContent', $tokenPath . '/timestampContentFile');
        $filesystem->copy(__DIR__ . '/../../resources/timestampContent.tsa', $tokenPath . '/timestampContentFile.tsa');

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/sittings/{$sitting->getId()}/token", [],[],
            ["HTTP_X-AUTH-TOKEN" => $apiUser->getToken()]);

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();

        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020_00_00_jetons.zip"', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }



}
