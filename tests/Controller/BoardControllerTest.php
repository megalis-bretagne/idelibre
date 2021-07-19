<?php

namespace App\Tests\Controller;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class BoardControllerTest extends WebTestCase
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
            StructureFixtures::class,
            UserFixtures::class,
            TypeFixtures::class,
            SittingFixtures::class,
            ConvocationFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/board');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Tableau de bord")');
        $this->assertCount(1, $item);
    }
}
