<?php

namespace App\Tests\Controller\api;

use App\Tests\FileTrait;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConvocationControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;
    use FileTrait;

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
        ConvocationStory::load();
        TypeStory::load();
        EmailTemplateStory::load();
        SittingStory::load();
        ApiUserStory::load();
    }

    public function testGetConvocations()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

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
        $sitting = SittingStory::sittingConseilLibriciel();
        UserStory::actorLibriciel1();
        EmailTemplateStory::emailTemplateConseilLs()->object();
        $convocation = ConvocationStory::convocationActor1();

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_POST, '/api/convocations/' . $convocation->getId() . '/send');
        $this->assertResponseStatusCodeSame(200);
        $this->assertNotEmpty($convocation->getSentTimestamp());

        $bag = static::getContainer()->getParameterBag();
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
        $convocation = ConvocationStory::convocationActor1();

        $data = [['convocationId' => $convocation->getId(), 'attendance' => 'absent', 'deputy' => 'John Doe', 'isRemote' => false]];

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
        $this->assertEquals('absent', $convocation->getAttendance());
        $this->assertEquals('John Doe', $convocation->getDeputy());
        $this->assertEquals(false, $convocation->isIsRemote() );
    }

    public function testSetAttendanceConvocationNotExists()
    {
        $randomUUID = 'ce854a57-0e0b-459e-b93e-53239680b30e';

        $data = [['convocationId' => $randomUUID, 'attendance' => 'absent', 'deputy' => 'John Doe', 'isRemote' => false]];

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
