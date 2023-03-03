<?php

namespace App\Tests\Controller;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class BoardControllerTest extends WebTestCase
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
