<?php

namespace App\Tests\Controller\api;

use App\Controller\api\SittingController;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SittingControllerTest extends WebTestCase
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
    }


}
