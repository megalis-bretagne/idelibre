<?php

namespace App\Tests\Controller\api;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\SittingStory;
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

        SittingStory::sittingConseilLibriciel();
    }

    public function testSendConvocations()
    {
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
        $sitting = SittingStory::sittingConseilLibriciel();
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/api/sittings/' . $sitting->getId() . '/notifyAgain', [], [], [], json_encode(['object' => 'test', 'content' => 'test']));
        $this->assertResponseStatusCodeSame(200);
    }
}
