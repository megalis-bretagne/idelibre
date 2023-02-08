<?php

namespace App\Tests\Controller;

use App\Entity\Convocation;
use App\Tests\Factory\AttendanceTokenFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConvocationStory;
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

    public function testIndex()
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
            '/attendance/confirmation/' . $token
        );
        $this->assertResponseStatusCodeSame(200);

        //dd($crawler);

        $item = $crawler->filter('html:contains("Merci de confirmer votre présence")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['attendance[attendance]'] = 'remote';
        $form['attendance[deputy]'] = null;

        $this->client->submit($form);

        $crawler->filter('h1')->children('div.badge-info')->count(1);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $crawler->filter('section')->children('div.alert')->count(1);

        $this->assertNotEmpty($this->getOneEntityBy(Convocation::class, ['attendance' => 'remote']));
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
