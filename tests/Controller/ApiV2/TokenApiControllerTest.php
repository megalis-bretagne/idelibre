<?php

namespace App\Tests\Controller\ApiV2;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TokenApiControllerTest extends WebTestCase
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

        StructureStory::libriciel();
        ApiUserStory::apiAdminLibriciel();
        SittingStory::sittingConseilLibriciel();
    }

    public function testGetSittingZipTokens()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();
        $sitting = SittingStory::sittingConseilLibriciel();

        $bag = self::getContainer()->get('parameter_bag');
        $year = $sitting->getDate()->format('Y');
        $tokenPath = "{$bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}";

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../../resources/timestampContent', $tokenPath . '/timestampContentFile');
        $filesystem->copy(__DIR__ . '/../../resources/timestampContent.tsa', $tokenPath . '/timestampContentFile.tsa');

        $this->loginAsAdminLibriciel();
        $this->client->request(
            Request::METHOD_GET,
            "/api/v2/structures/{$structure->getId()}/sittings/{$sitting->getId()}/token",
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $apiUser->getToken()]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();

        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020_00_00_jetons.zip"', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }
}
