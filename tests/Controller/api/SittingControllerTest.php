<?php

namespace App\Tests\Controller\api;

use App\Service\Connector\ComelusConnectorManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\SittingStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SittingControllerTest extends WebTestCase
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
    }

    public function testSendConvocations()
    {
        UserStory::load();
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_POST, '/api/sittings/' . $sitting->getId() . '/sendConvocations');

        $this->assertResponseStatusCodeSame(200);

        foreach ($sitting->getConvocations() as $convocation) {
            $this->assertNotEmpty($convocation->getSentTimestamp());
        }

        // TODO CHECK GENERATED FILES !
    }

    public function testNotifyAgain()
    {
        UserStory::load();
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/api/sittings/' . $sitting->getId() . '/notifyAgain', [], [], [], json_encode(['object' => 'test', 'content' => 'test']));
        $this->assertResponseStatusCodeSame(200);
    }


    public function testGetSitting()
    {
        UserStory::load();
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/sittings/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertSame($sitting->getId(), $content->id);
    }

    public function testSendComelus()
    {
        UserStory::load();
        $sitting = SittingStory::sittingConseilLibriciel();

        $ComelusMock = $this->getMockBuilder(ComelusConnectorManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ComelusMock->method('sendComelus')->willReturn('comelusUUID');
        $container = self::getContainer();
        $container->set(ComelusConnectorManager::class, $ComelusMock);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, "/api/sittings/{$sitting->getId()}/sendComelus");
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertSame('comelusUUID', $content->comelusId);
    }


    public function testGetMaxSittingSizeForGeneration()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, "/api/sittings/maxSize");
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertNotEmpty($content->maxSize);
    }

    public function testGetMaxFileSizeForGeneration()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, "/api/sittings/fileMaxSize");
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertNotEmpty($content->fileMaxSize);
    }

    public function testGetCurrentStructureSittingTimezone()
    {
        UserStory::load();
        $sitting = SittingStory::sittingConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, "/api/sittings/{$sitting->getId()}/timezone");
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertNotEmpty($content->timezone);
    }




}
