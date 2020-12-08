<?php

namespace App\Tests\Controller\api;

use App\Controller\api\ConvocationController;
use App\DataFixtures\ConvocationFixtures;
use App\Tests\FileTrait;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class ConvocationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;
    use FileTrait;


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
            ConvocationFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testGetConvocations()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/api/convocations/' . $sitting->getId());
        $this->assertResponseStatusCodeSame(200);

        $convocations = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $convocations);
    }


    public function testSendConvocation()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $actor = $this->getOneUserBy(['username' => 'actor1@libriciel.coop']);
        $convocation = $this->getOneConvocationBy(['sitting' => $sitting, 'actor' => $actor]);
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/api/convocations/' . $convocation->getId() . '/send');
        $this->assertResponseStatusCodeSame(200);

        $this->entityManager->refresh($convocation);

        $this->assertNotEmpty($convocation->getSentTimestamp());

        $bag = self::$container->get('parameter_bag');

        $year = $sitting->getDate()->format("Y");
        $tokenPath = "{$bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}";
        $this->assertEquals(2, $this->countFileInDirectory($tokenPath));
    }
}
