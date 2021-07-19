<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\EmailTemplateFixtures;
use App\DataFixtures\SittingFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SittingControllerTest extends WebTestCase
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
            SittingFixtures::class,
            ConvocationFixtures::class,
            EmailTemplateFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testSendConvocations()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_POST, '/api/sittings/' . $sitting->getId() . '/sendConvocations');

        $this->assertResponseStatusCodeSame(200);

        $this->entityManager->refresh($sitting);

        foreach ($sitting->getConvocations() as $convocation) {
            $this->assertNotEmpty($convocation->getSentTimestamp());
        }

        // TODO CHECK GENERATED FILES !
    }

    public function testNotifyAgain()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/api/sittings/' . $sitting->getId() . '/notifyAgain', [], [], [], json_encode(['object' => 'test', 'content' => 'test']));
        $this->assertResponseStatusCodeSame(200);
    }
}
