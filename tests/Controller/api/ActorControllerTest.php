<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ActorControllerTest extends WebTestCase
{
    use FixturesTrait;
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

        $this->loadFixtures([
            UserFixtures::class,
            SittingFixtures::class,
            ConvocationFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testGetActors()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors');
        $this->assertResponseStatusCodeSame(200);

        $actors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(3, $actors);
        $this->assertSame('libriciel', $actors[0]['lastName']);
    }

    public function testGetActorsInSitting()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors/sittings/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $actorsInSitting = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $actorsInSitting);
    }


    public function testGetActorsNotInSitting()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors/sittings/' . $sitting->getId() . '/not');
        $this->assertResponseStatusCodeSame(200);

        $actorsNotInSitting = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $actorsNotInSitting);
    }


    public function testUpdateActorsInSittingAddActor()
    {
        $actor3 = $this->getOneUserBy(['username' => 'actor3@libriciel.coop']);
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $data = json_encode(['addedActors' => [$actor3->getId()], 'removedActors' => []]);

        $this->client->request(
            Request::METHOD_PUT,
            '/api/actors/sittings/' . $sitting->getId(),
            [],
            [],
            [],
            $data
        );

        $this->assertResponseStatusCodeSame(200);
        $this->entityManager->refresh($sitting);

        $this->assertCount(3, $sitting->getConvocations());
    }

    public function testUpdateActorsInSittingRemoveActor()
    {
        $actor1 = $this->getOneUserBy(['username' => 'actor1@libriciel.coop']);
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $data = json_encode(['addedActors' => [], 'removedActors' => [$actor1->getId()]]);

        $this->client->request(
            Request::METHOD_PUT,
            '/api/actors/sittings/' . $sitting->getId(),
            [],
            [],
            [],
            $data
        );

        $this->assertResponseStatusCodeSame(200);
        $this->entityManager->refresh($sitting);

        $this->assertCount(1, $sitting->getConvocations());
    }

    public function testGetActorsConvocationSent()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors/sittings/' . $sitting->getId() . '/sent');
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $content);
    }
}
