<?php

namespace App\Tests\Controller;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\TimestampFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class ReportSittingControllerTest extends WebTestCase
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
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    /**
     * Problem absolute_url with webpack. Can't access to localhost/build/app.xxx.css
     * works well in real life.
     */
    /*public function testPdfReport()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/reportSitting/pdf/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename="Conseil Libriciel_rapport.pdf"', $response->headers->get('content-disposition'));
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertGreaterThan(5000, intval($response->headers->get('content-length')));
    }
*/
    public function testCsvReport()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/reportSitting/csv/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename="Conseil Libriciel_rapport.csv"', $response->headers->get('content-disposition'));
        $this->assertSame('text/plain', $response->headers->get('content-type'));
        $this->assertGreaterThan(20, intval($response->headers->get('content-length')));
    }

    public function testGetSittingZipTokens()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $bag = self::$container->get('parameter_bag');
        $year = $sitting->getDate()->format('Y');
        $tokenPath = "{$bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}";

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/timestampContent', $tokenPath . '/timestampContentFile');
        $filesystem->copy(__DIR__ . '/../resources/timestampContent.tsa', $tokenPath . '/timestampContentFile.tsa');

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/reportSitting/token/' . $sitting->getId());

        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();

        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename="Conseil Libriciel_22_10_2020_00_00_jetons.zip"', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }
}
