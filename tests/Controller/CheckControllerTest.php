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

class CheckControllerTest extends WebTestCase
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

    public function testIndex()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/check');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Vérification de la plateforme")');
        $this->assertCount(1, $item);
    }
}
