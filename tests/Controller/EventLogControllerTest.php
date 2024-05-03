<?php

namespace App\Tests\Controller;

use App\Controller\EventLogController;
use App\Tests\Factory\EventLog\EventLogFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EventLogControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
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
        $libriciel = StructureStory::libriciel();

        EventLogFactory::createOne([
            'structureId' => $libriciel->getId(),
        ]);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/eventLog');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Journal des événements")');
        $this->assertCount(1, $item);
    }
}
