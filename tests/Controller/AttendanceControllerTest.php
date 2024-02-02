<?php

namespace App\Tests\Controller;

use App\Entity\Convocation;
use App\Entity\User;
use App\Service\Util\GenderConverter;
use App\Tests\Factory\AttendanceTokenFactory;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\PartyStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AttendanceControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use LoginTrait;
    use FindEntityTrait;

    private ?KernelBrowser $client;
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel
            ->getContainer()
            ->get('doctrine')->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testAttendanceIsPresent()
    {
        $userNoDeputy = UserFactory::createOne([
            'username' => 'actorNoDeputy@libriciel',
            'email' => 'actorNoDeputy@example.org',
            'password' => UserStory::PASSWORD,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => GenderConverter::MALE,
            'title' => 'Mr le maire',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::actor(),
            'party' => PartyStory::majorite(),
            'deputy' => null
        ]);

        $convocation = ConvocationFactory::createOne([
            'sitting' => SittingStory::sittingConseilWithTokenSent(),
            'user' => $userNoDeputy,
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
        ]);

        AttendanceTokenFactory::createOne([
            'token' => 'mytoken',
            'convocation' => $convocation,
        ]);
        $token = $convocation->getAttendancetoken()->getToken();

        $this->loginAsUserMontpellier();
        $crawler = $this->client->request(Request::METHOD_GET, '/attendance/confirmation/' . $token);
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('title')->text("Confirmation de présence à la séance");
        $this->assertSame("Confirmation de présence à la séance", $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['attendance[attendance]'] = 'present';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $alert = $crawler->filter('section')->children('div.alert')->count();
        $this->assertSame(1, $alert);

        $this->assertNotEmpty($this->getOneEntityBy(Convocation::class, ['attendance' => 'present']));
    }

    public function testAttendanceIsReplacedbyMandator()
    {
        $user = UserStory::actorLibriciel1()->object();
        $mandator = UserFactory::createOne();


        $convocation = ConvocationFactory::createOne([
            'sitting' => SittingStory::sittingConseilLibriciel(),
            'user' => $user,
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
            'mandator' => null,
            'deputy' => null
        ]);

        $convocation2  = ConvocationFactory::createOne([
            'sitting' => SittingStory::sittingConseilLibriciel(),
            'user' => $mandator,
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
            'mandator' => null,
            'deputy' => null
        ]);

        AttendanceTokenFactory::createOne([
            'token' => 'mytoken',
            'convocation' => $convocation,
        ]);
        $token = $convocation->getAttendancetoken()->getToken();


        $this->loginAsUserMontpellier();
        $crawler = $this->client->request(
            Request::METHOD_GET,
            '/attendance/confirmation/' . $token
        );
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Merci de confirmer votre présence")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['attendance[attendance]'] = 'poa';
        $form['attendance[mandataire]'] = $convocation2->getUser()->getId();

        $this->client->submit($form);

        $crawler->filter('h1')->children('div.badge-info')->count(1);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $alert = $crawler->filter('section')->children('div.alert')->count();
        $this->assertSame(1, $alert);

        $this->assertNotEmpty($this->getOneEntityBy(Convocation::class, ['attendance' => 'poa']));
    }



    public function testAttendanceIsAbsentReplacedByDeputy()
    {
        $convocation = ConvocationStory::convocationActor3SentWithToken();
        $user = UserStory::actorWithDeputy()->object();
        $deputy = $user->getDeputy();

        AttendanceTokenFactory::createOne([
            'token' => 'mytoken',
            'convocation' => $convocation,
        ]);
        $token = $convocation->getAttendancetoken()->getToken();

        $this->loginAsUserMontpellier();
        $crawler = $this->client->request(
            Request::METHOD_GET,
            '/attendance/confirmation/' . $token
        );
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Merci de confirmer votre présence")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['attendance[attendance]'] = 'deputy';

        $this->client->submit($form);

        $crawler->filter('h1')->children('div.badge-info')->count(1);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $alert = $crawler->filter('section')->children('div.alert')->count();
        $this->assertSame(1, $alert);

        $this->assertNotEmpty($this->getOneEntityBy(Convocation::class, ['attendance' => 'deputy', "deputy" => $user->getDeputy()->getId()]));
    }

    public function testAttendanceRedirect()
    {
        $convocation = ConvocationStory::convocationActor2SentWithToken();
        AttendanceTokenFactory::createOne([
            'token' => 'mytoken',
            'convocation' => $convocation,
        ]);
        $token = $convocation->getAttendancetoken()->getToken();

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(
            Request::METHOD_GET,
            '/attendance/redirect/' . $token
        );
        $this->assertResponseStatusCodeSame(200);

        $crawler->filter('section')->children('div.alert')->count(1);
    }
}
