<?php

namespace App\Tests\Controller\api;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserControllerTest extends WebTestCase
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


        UserStory::actorLibriciel3();
        SittingStory::sittingConseilLibriciel();
        ConvocationStory::load();
    }

    public function testGetActors()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/actors');
        $this->assertResponseStatusCodeSame(200);

        $actors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(4, $actors);
    }

    public function testGetUsersInSitting()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
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
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/users/sittings/' . $sitting->getId() . '/not');
        $this->assertResponseStatusCodeSame(200);

        $usersNotInSitting = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $usersNotInSitting['actors']);
        $this->assertCount(2, $usersNotInSitting['employees']);
        $this->assertCount(0, $usersNotInSitting['guests']);
    }

    public function testUpdateUsersInSittingAddActor()
    {
        $actor3 = UserStory::actorLibriciel3();
        $sitting = SittingStory::sittingConseilLibriciel();
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
        UserStory::actorLibriciel3()->refresh($sitting);

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
        UserStory::actorLibriciel1()->refresh($sitting);

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
