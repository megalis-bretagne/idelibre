<?php

namespace App\Tests\Controller;

use App\DataFixtures\RoleFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Role;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
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
            RoleFixtures::class,
            TypeFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'otherUser@libriciel']);
        $crawler = $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("l\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['id' => $user->getId()]));
    }

    public function testDeleteOtherStructureUser()
    {
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'user@montpellier']);
        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteMyself()
    {
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'admin@libriciel']);
        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Impossible de supprimer son propre utilisateur")');
        $this->assertCount(1, $successMsg);
    }

    public function testAdd()
    {
        /** @var Role $adminRole */
        $adminRole = $this->getOneEntityBy(Role::class, ['name' => 'Admin']);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/user/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['user[firstName]'] = 'new';
        $form['user[lastName]'] = 'user';
        $form['user[username]'] = 'newuser';
        $form['user[email]'] = 'newuser@example.org';
        $form['user[role]'] = $adminRole->getId();
        $form['user[plainPassword][first]'] = 'password';
        $form['user[plainPassword][second]'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("votre utilisateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'newuser']));
    }

    public function testEdit()
    {
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'otherUser@libriciel']);
        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['user[firstName]'] = 'new';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testEditSecretary()
    {
        $this->loginAsAdminLibriciel();

        $user = $this->getOneUserBy(['username' => 'secretary1@libriciel.coop']);
        $type =$this->getOneTypeBy(['name' => 'Bureau Communautaire Libriciel']);

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['user[authorizedTypes]'] = [$type->getId()];
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $this->entityManager->refresh($user);
        $this->assertSame($user->getAuthorizedTypes()->first()->getId(), $type->getId());
    }


    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/user');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Utilisateurs")');
        $this->assertCount(1, $item);
    }

    public function testPreferences()
    {
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/preferences');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Préférences utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['user_preference[username]'] = 'New username';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Vos préférences utilisateur ont bien été modifiées")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($user = $this->getOneEntityBy(User::class, ['username' => 'New username']));
    }
}
