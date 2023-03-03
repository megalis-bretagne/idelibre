<?php

namespace App\Tests\Controller;

use App\Controller\StatisticController;
use App\Entity\Structure;
use App\Security\Password\LegacyPassword;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class StatisticControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ?LegacyPassword $legacyPassword;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }


    public function testIndex()
    {
        UserStory::load();
        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/statistic' );
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Statistiques")');
        $this->assertCount(1, $item);
    }

    public function testCountUsers()
    {
        UserStory::load();
        $this->loginAsSuperAdmin();
        $this->client->request(Request::METHOD_GET, '/statistic/user');
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame("attachment; filename=user_structure_rapport.csv", $response->headers->get('content-disposition'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }


    public function testSittingsInfoAfter()
    {
        UserStory::load();
        $this->loginAsSuperAdmin();
        $this->client->request(Request::METHOD_GET, 'statistic/sitting');
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('content-disposition'));
        $this->assertSame("attachment; filename=convocation_structure_rapport.csv", $response->headers->get('content-disposition'));
        $this->assertGreaterThan(100, intval($response->headers->get('content-length')));
    }





}
