<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\EmailTemplateFixtures;
use App\DataFixtures\TypeFixtures;
use App\Tests\FileTrait;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ConvocationControllerTest extends WebTestCase
{
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

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            ConvocationFixtures::class,
            TypeFixtures::class,
            EmailTemplateFixtures::class,
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

        $this->assertCount(2, $convocations['actors']);
        $this->assertCount(0, $convocations['employees']);
        $this->assertCount(0, $convocations['guests']);
    }

    public function testSendConvocation()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $actor = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $convocation = $this->getOneConvocationBy(['sitting' => $sitting, 'user' => $actor]);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_POST, '/api/convocations/' . $convocation->getId() . '/send');
        $this->assertResponseStatusCodeSame(200);

        $this->entityManager->refresh($convocation);

        $this->assertNotEmpty($convocation->getSentTimestamp());

        $bag = self::$container->get('parameter_bag');

        $year = $sitting->getDate()->format('Y');
        $tokenPath = "{$bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}";
        $this->assertEquals(2, $this->countFileInDirectory($tokenPath));
    }

    public function testSetAttendanceNoLogin()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/convocations/attendance',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(302);
    }

    public function testSetAttendance()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $actor = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $convocation = $this->getOneConvocationBy(['sitting' => $sitting, 'user' => $actor]);

        $data = [['convocationId' => $convocation->getId(), 'attendance' => 'absent', 'deputy' => 'John Doe']];

        $this->loginAsAdminLibriciel();

        $this->client->request(
            Request::METHOD_POST,
            '/api/convocations/attendance',
            [],
            [],
            [],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(200);
        $this->entityManager->refresh($convocation);
        $this->assertEquals('absent', $convocation->getAttendance());
        $this->assertEquals('John Doe', $convocation->getDeputy());
    }

    public function testSetAttendanceConvocationNotExists()
    {
        $randomUUID = 'ce854a57-0e0b-459e-b93e-53239680b30e';

        $data = [['convocationId' => $randomUUID, 'attendance' => 'absent', 'deputy' => 'John Doe']];

        $this->loginAsAdminLibriciel();

        $this->client->request(
            Request::METHOD_POST,
            '/api/convocations/attendance',
            [],
            [],
            [],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(403);
    }
}
