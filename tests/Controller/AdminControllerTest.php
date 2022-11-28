<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
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
        $this->loginAsSuperAdmin();
        $user = $this->getOneEntityBy(User::class, ['username' => 'otherSuperadmin']);

        $this->client->request(Request::METHOD_DELETE, '/admin/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("L\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['id' => $user->getId()]));
    }

    public function testDeleteYourself()
    {
        $this->loginAsSuperAdmin();
        $user = $this->getOneEntityBy(User::class, ['username' => 'superadmin']);
        $this->client->request(Request::METHOD_DELETE, '/admin/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Impossible de supprimer son propre utilisateur")');
        $this->assertCount(1, $successMsg);
    }

    public function testAdd()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un administrateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['super_user[firstName]'] = 'new';
        $form['super_user[lastName]'] = 'admin';
        $form['super_user[email]'] = 'newadmin@example.org';
        $form['super_user[username]'] = 'newadmin';
        $form['super_user[plainPassword][first]'] = 'password';
        $form['super_user[plainPassword][second]'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);
        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'newadmin']));
    }

    public function testEdit()
    {
        $this->loginAsSuperAdmin();
        $admin = UserStory::superadmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/edit/' . $admin->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un administrateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['super_user[firstName]'] = 'new';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testAddGroupAdmin()
    {
        $group = GroupStory::recia();
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/group/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un administrateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['super_user[firstName]'] = 'new';
        $form['super_user[lastName]'] = 'admin';
        $form['super_user[email]'] = 'newadmin@example.org';
        $form['super_user[username]'] = 'newAdmin';
        $form['super_user[plainPassword][first]'] = 'password';
        $form['super_user[plainPassword][second]'] = 'password';
        $form['super_user[group]'] = $group->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre administrateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);
    }

    public function testIndex()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/admin');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Administrateurs de la plateforme")');
        $this->assertCount(1, $item);
    }

    public function testIndexNotSuperadmin()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/admin');
        $this->assertResponseStatusCodeSame(403);
    }
}
