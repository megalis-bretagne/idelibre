<?php

namespace App\Tests\Controller\api;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ThemeControllerTest extends WebTestCase
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

        ThemeStory::load();
        UserStory::load();
    }

    public function testGetThemes()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/themes');
        $this->assertResponseStatusCodeSame(200);

        $themes = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(4, $themes);
    }
}
