<?php

namespace App\Tests\Controller;

use App\Tests\Factory\SittingFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReportSittingControllerTest extends WebTestCase
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

        SittingStory::load();
        ConvocationStory::load();
    }

    /**
     * Problem absolute_url with webpack. Can't access to localhost/build/app.xxx.css
     * works well in real life.
     * todo authorize interal conf in snappy for tests cf lsvote
     */
    /*
      public function testPdfReport()
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
        $this->assertSame('attachment; filename=conseil_libriciel_rapport.csv', $response->headers->get('content-disposition'));
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertGreaterThan(20, intval($response->headers->get('content-length')));
    }


    public function testCsvReportWithForbiddenChar()
    {
        $sitting = SittingFactory::createOne(['name' => 'Conseil Libriciel/', 'structure' => StructureStory::libriciel()]);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/reportSitting/csv/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame('attachment; filename=conseil_libriciel__rapport.csv', $response->headers->get('content-disposition'));
        $this->assertGreaterThan(20, intval($response->headers->get('content-length')));
    }

    public function testGetSittingZipTokens()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $bag = self::getContainer()->get('parameter_bag');
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
        $this->assertSame('attachment; filename=conseil_libriciel_22_10_2020_00_00_jetons.zip', $response->headers->get('content-disposition'));
        $this->assertSame('application/zip', $response->headers->get('content-type'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }
}
