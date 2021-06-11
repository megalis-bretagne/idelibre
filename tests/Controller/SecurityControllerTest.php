<?php

namespace App\Tests\Controller;

use App\DataFixtures\ForgetTokenFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;
    /**
     * @var \Doctrine\Persistence\ObjectManager
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
            ForgetTokenFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }

    public function testImpersonateAS()
    {
        $this->loginAsSuperAdmin();
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        $this->client->request(Request::METHOD_GET, '/security/impersonate/' . $structure->getId());
        $this->assertResponseRedirects();

        $crawler = $this->client->followRedirect();

        $flash = $crawler->filter('html:contains("Vous êtes connecté dans la structure")');
        $this->assertCount(1, $flash);
    }

    public function testImpersonateASNotSuperAdmin()
    {
        $this->loginAsAdminLibriciel();
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        $this->client->request(Request::METHOD_GET, '/security/impersonate/' . $structure->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testResetPasswordwrongToken()
    {
        $this->client->request(Request::METHOD_GET, '/reset/aqwx');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testResetPassword()
    {
        $this->client->request(Request::METHOD_GET, '/reset/forgetToken');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testForgetPasswordGet()
    {
        $this->client->request(Request::METHOD_GET, '/forget');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testForgetPasswordWrongEmail()
    {
        $this->client->request(Request::METHOD_POST, '/forget', ['username' => 'notexist']);
        $this->assertResponseStatusCodeSame(302);
    }

    public function testForgetPassword()
    {
        $this->client->request(Request::METHOD_POST, '/forget', ['username' => 'superadmin']);
        $this->assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $flash = $crawler->filter('html:contains(" Un email vous a été envoyé si un compte lui est associé")');
        $this->assertCount(1, $flash);
    }

    public function testLogout()
    {
        $this->loginAsSuperAdmin();
        $this->client->request(Request::METHOD_GET, '/logout');
        $this->assertResponseRedirects();

        $crawler = $this->client->followRedirect();

        $item = $crawler->filter('html:contains("Mot de passe oublié")');
        $this->assertCount(1, $item);
    }

    public function testImpersonateExit()
    {
        $this->loginAsSuperAdmin();
        $this->client->request(Request::METHOD_GET, '/security/impersonateExit');
        $this->assertResponseRedirects();

        $crawler = $this->client->followRedirect();

        $flash = $crawler->filter('html:contains("Vous n\'êtes plus connecté dans une structure")');
        $this->assertCount(1, $flash);
    }

    public function testLogin()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/login');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Veuillez saisir vos identifiants de connexion")');

        $this->assertCount(1, $title);

        $form = $crawler->selectButton('Se connecter')->form();

        $form['username'] = 'superadmin';
        $form['password'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Structures")');

        $this->assertCount(1, $successMsg);
    }

    public function testLoginLegacy()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/login');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Veuillez saisir vos identifiants de connexion")');

        $this->assertCount(1, $title);

        $form = $crawler->selectButton('Se connecter')->form();

        $form['username'] = 'userLegacy@montpellier';
        $form['password'] = 'passwordLegacy';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Séances")');

        $this->assertCount(1, $successMsg);

        $updatedPasswordUser = $this->getOneUserBy(['username' => 'userLegacy@montpellier']);
        $this->assertSame('$', $updatedPasswordUser->getPassword()[0]);
    }

    public function testLoginFalse()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/login');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Veuillez saisir vos identifiants de connexion")');

        $this->assertCount(1, $title);

        $form = $crawler->selectButton('Se connecter')->form();

        $form['username'] = 'superadmin';
        $form['password'] = 'false';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("erreur d\'identification")');

        $this->assertCount(1, $successMsg);
    }

    public function testLoginInactive()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/login');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Veuillez saisir vos identifiants de connexion")');

        $this->assertCount(1, $title);

        $form = $crawler->selectButton('Se connecter')->form();

        $form['username'] = 'superadminInactive';
        $form['password'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("erreur d\'identification")');

        $this->assertCount(1, $successMsg);
    }
}
