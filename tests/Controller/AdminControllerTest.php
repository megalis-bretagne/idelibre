<?php

namespace App\Tests\Controller;

use App\DataFixtures\GroupFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Group;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AdminControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManagerInterface
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
            GroupFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->getConnection()->close();
    }


    public function testDelete()
    {
        $this->loginAsSuperAdmin();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'otherSuperadmin']);
        $this->client->request(Request::METHOD_DELETE, '/admin/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("l\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);
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

        $successMsg = $crawler->filter('html:contains("votre administrateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);
    }

    public function testEdit()
    {
        $this->loginAsSuperAdmin();
        $admin = $this->getOneEntityBy(User::class, ['username' => 'superadmin']);
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
        $successMsg = $crawler->filter('html:contains("votre administrateur a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testAddGroupAdmin()
    {
        $group = $this->getOneEntityBy(Group::class, ['name' => 'Recia']);
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/group/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un administrateur")');
        $this->assertCount(1, $item);

        ;

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

        $successMsg = $crawler->filter('html:contains("votre administrateur a bien été ajouté")');
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
