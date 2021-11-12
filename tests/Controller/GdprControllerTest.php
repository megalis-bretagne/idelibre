<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class GdprControllerTest extends WebTestCase
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
            UserFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testEditHosting()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/edit');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier les informations RGPD de la plateforme")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['gdpr_hosting[companyName]'] = 'Libriciel';
        $form['gdpr_hosting[address]'] = '836 rue du mas de verchant';
        $form['gdpr_hosting[companyPhone]'] = '0102030405';
        $form['gdpr_hosting[companyEmail]'] = 'email@exemple.org';
        $form['gdpr_hosting[representative]'] = 'el presidente';
        $form['gdpr_hosting[quality]'] = 'president';
        $form['gdpr_hosting[siret]'] = '1234544';
        $form['gdpr_hosting[ape]'] = '345';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Vos informations RGPD ont été mises à jour")');
        $this->assertCount(1, $successMsg);
    }

    public function testEditDataController()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/editController');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier les informations RGPD du responsable des traitements")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['data_controller_gdpr[name]'] = 'Ville de Montpellier';
        $form['data_controller_gdpr[address]'] = '836 rue du mas de verchant';
        $form['data_controller_gdpr[phone]'] = '0102030405';
        $form['data_controller_gdpr[email]'] = 'email@exemple.org';
        $form['data_controller_gdpr[representative]'] = 'el presidente';
        $form['data_controller_gdpr[quality]'] = 'president';
        $form['data_controller_gdpr[siret]'] = '1234544';
        $form['data_controller_gdpr[ape]'] = '345';
        $form['data_controller_gdpr[dpoName]'] = 'M. Thomas Durant';
        $form['data_controller_gdpr[dpoEmail]'] = 't.durant@exemple.org';

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
