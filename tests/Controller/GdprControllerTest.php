<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class GdprControllerTest extends WebTestCase
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
            UserFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testEdit()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/edit');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier les informations RGPD")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['gdpr[companyName]'] = 'Libriciel';
        $form['gdpr[address]'] = '836 rue du mas de verchant';
        $form['gdpr[companyPhone]'] = '0102030405';
        $form['gdpr[companyEmail]'] = 'email@exemple.org';
        $form['gdpr[representative]'] = 'el presidente';
        $form['gdpr[quality]'] = 'president';
        $form['gdpr[siret]'] = '1234544';
        $form['gdpr[ape]'] = '345';
        $form['gdpr[dpoEmail]'] = 'dpo@example.org';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Vos informations RGPD ont été mises à jour")');
        $this->assertCount(1, $successMsg);
    }

    public function testNotice()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/notice');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Page de politique de confidentialité")');
        $this->assertCount(1, $item);
    }
}
