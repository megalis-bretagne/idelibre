<?php

namespace App\Tests\Controller;

use App\Entity\Structure;
use App\Entity\User;
use App\Security\Password\LegacyPassword;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ForgetTokenStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ?LegacyPassword $legacyPassword;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $this->legacyPassword = self::getContainer()->get(LegacyPassword::class);

        UserStory::load();
        ForgetTokenStory::load();
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

        $user = $this->getOneUserBy(['username' => 'superadmin']);
        $forgetToken = $this->getOneForgetTokenBy(['user' => $user]);
        $this->assertNotEmpty($forgetToken);
    }

    public function testForgetPasswordAlreadyForgetToken()
    {
        $this->client->request(Request::METHOD_POST, '/forget', ['username' => 'admin@libriciel']);
        $this->assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $flash = $crawler->filter('html:contains(" Un email vous a été envoyé si un compte lui est associé")');
        $this->assertCount(1, $flash);

        $user = $this->getOneUserBy(['username' => 'admin@libriciel']);
        $forgetToken = $this->getOneForgetTokenBy(['user' => $user]);
        $this->assertNotEmpty($forgetToken);
        $this->assertNotSame('forgetToken', $forgetToken->getToken());
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
        $userLegacy = UserFactory::createOne([
            'email' => 'userLegacy@example.org',
            'username' => 'userLegacy@montpellier',
            'firstname' => 'userLegacy',
            'lastname' => 'montpellier',
            'role' => RoleStory::admin(),
            'structure' => StructureStory::montpellier(),
            'password' => $this->legacyPassword->encode('passwordLegacy'),
            'isActive' => true,
        ]);

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

        $this->assertSame('$', $userLegacy->getPassword()[0]);
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

    public function testChangePasswordJson()
    {
        /** @var User $actor */
        $actor = UserStory::actorLibriciel1();
        $oldEncodedPassword = $actor->getPassword();

        $data = [
            'passPhrase' => 'passphrase',
            'userId' => $actor->getId(),
            'plainNewPassword' => 'AZERTyuiop12345!AZERTyuiop12345!',
            'plainCurrentPassword' => 'password',
        ];
        $this->client->request(Request::METHOD_POST, '/security/changePassword', content: json_encode($data));

        $this->assertResponseStatusCodeSame(200);
        $this->assertNotSame($oldEncodedPassword, $actor->getPassword());
    }

    public function testChangePasswordJsonFakePassphrase()
    {
        $actor = UserStory::actorLibriciel1();

        $data = [
            'passPhrase' => 'fakePassphrase',
            'userId' => $actor->getId(),
            'plainNewPassword' => 'AZERTyuiop12345!AZERTyuiop12345!',
            'plainCurrentPassword' => 'password',
        ];
        $this->client->request(Request::METHOD_POST, '/security/changePassword', content: json_encode($data));

        $this->assertResponseStatusCodeSame(403);
    }
}
