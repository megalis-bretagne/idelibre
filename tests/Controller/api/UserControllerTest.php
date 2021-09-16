<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends WebTestCase
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
            UserFixtures::class,
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

    public function testGetActors()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors');
        $this->assertResponseStatusCodeSame(200);

        $actors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(5, $actors);
        $this->assertSame('Gille', $actors[0]['lastName']);
    }

    public function testGetUsersInSitting()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/users/sittings/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $usersInSitting = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $usersInSitting['actors']);
        $this->assertCount(0, $usersInSitting['employees']);
        $this->assertCount(0, $usersInSitting['guests']);
    }

    public function testGetUsersNotInSitting()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/users/sittings/' . $sitting->getId() . '/not');
        $this->assertResponseStatusCodeSame(200);

        $usersNotInSitting = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $usersNotInSitting['actors']);
        $this->assertCount(5, $usersNotInSitting['employees']);
        $this->assertCount(2, $usersNotInSitting['guests']);
    }

    public function testUpdateUsersInSittingAddActor()
    {
        $actor3 = $this->getOneUserBy(['username' => 'actor3@libriciel']);
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $data = json_encode(['addedActors' => [$actor3->getId()], 'addedEmployees' => [], 'addedGuests' => [],  'removedUsers' => []]);

        $this->client->request(
            Request::METHOD_PUT,
            '/api/users/sittings/' . $sitting->getId(),
            [],
            [],
            [],
            $data
        );

        $this->assertResponseStatusCodeSame(200);
        $this->entityManager->refresh($sitting);

        $this->assertCount(3, $sitting->getConvocations());
    }

    public function testUpdateUsersInSittingRemoveActor()
    {
        $actor1 = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $data = json_encode(['addedActors' => [], 'addedEmployees' => [], 'addedGuests' => [], 'removedUsers' => [$actor1->getId()]]);

        $this->client->request(
            Request::METHOD_PUT,
            '/api/users/sittings/' . $sitting->getId(),
            [],
            [],
            [],
            $data
        );

        $this->assertResponseStatusCodeSame(200);
        $this->entityManager->refresh($sitting);

        $this->assertCount(1, $sitting->getConvocations());
    }

    public function testGetUsersConvocationSent()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/users/sittings/' . $sitting->getId() . '/sent');
        $this->assertResponseStatusCodeSame(200);
        $userConvocationsSent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $userConvocationsSent['actors']);
        $this->assertCount(0, $userConvocationsSent['employees']);
        $this->assertCount(0, $userConvocationsSent['guests']);
    }
}
