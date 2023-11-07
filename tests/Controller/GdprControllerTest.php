<?php

namespace App\Tests\Controller;

use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GdprControllerTest extends WebTestCase
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

        UserStory::load();
    }

    public function testEditHosting()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/editHosting');
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

        $form = $crawler->selectButton('Enregister les informations')->form();

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
        $this->loginAsSecretaryLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/gdpr/notice');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Page de politique de confidentialité")');
        $this->assertCount(1, $item);
    }
}
