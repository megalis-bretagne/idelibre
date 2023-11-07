<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\User\PasswordInvalidator;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConfigurationStory;
use App\Tests\Story\GroupStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AdminControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();

        $this->client = static::createClient();

        UserStory::load();
        GroupStory::recia();
    }

    public function testDelete()
    {
        $superadmin = UserFactory::createOne([
            'role' => RoleStory::superadmin(),
        ])->object();

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_DELETE, '/admin/delete/' . $superadmin->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("L\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);


        $this->assertEmpty($superadmin->getId());
    }

    public function testDeleteYourself()
    {
        $user = $this->getOneEntityBy(User::class, [
            'username' => 'superadmin',
        ]);

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_DELETE, '/admin/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Impossible de supprimer son propre utilisateur")');
        $this->assertCount(1, $successMsg);
    }

    public function testAdd()
    {
        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajout d\'un administrateur');

        $form = $crawler->selectButton('Ajouter l\'administrateur')->form();

        $form['super_user[firstName]'] = 'new';
        $form['super_user[lastName]'] = 'admin';
        $form['super_user[email]'] = 'newadmin@example.org';
        $form['super_user[username]'] = 'newadmin';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(User::class, [
            'username' => 'newadmin',
        ]));
    }

    public function testEdit()
    {
        $admin = UserStory::superadmin();

        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/edit/' . $admin->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Modifier un administrateur');

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['super_user[firstName]'] = 'new';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testAddGroupAdmin()
    {
        $group = GroupStory::recia();

        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/group/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajout d\'un administrateur');

        $form = $crawler->selectButton('Ajouter l\'administrateur')->form();

        $form['super_user[firstName]'] = 'new';
        $form['super_user[lastName]'] = 'admin';
        $form['super_user[email]'] = 'newadmin@example.org';
        $form['super_user[username]'] = 'newAdmin';
        $form['super_user[group]'] = $group->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);
    }

    public function testIndex()
    {
        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_GET, '/admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Administrateurs de la plateforme');
    }

    public function testIndexNotSuperadmin()
    {
        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_GET, '/admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testInvalidateAdminPassword() {

        $admin = UserFactory::createOne([
            'role' => RoleStory::groupadmin(),
        ])->object();

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_POST, '/admin_invalidate_password/' . $admin->getId() );
        $this->assertSame(PasswordInvalidator::INVALID_PASSWORD, $admin->getPassword());

    }

    public function testInvalidateUserPassword() {

        ConfigurationStory::load();
        UserStory::load();
        $groupAdmin = UserFactory::createOne(['structure' => StructureStory::libriciel(), 'role' => RoleStory::groupadmin()]);
        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_POST, '/admin_invalidate_password/' . $groupAdmin->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Un e-mail de réinitialisation du mot de passe a été envoyé")');
        $this->assertCount(1, $successMsg);
        $this->assertSame(PasswordInvalidator::INVALID_PASSWORD, $groupAdmin->getPassword());

    }
}
