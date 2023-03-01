<?php

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\PasswordInvalidator;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConfigurationStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\SubscriptionStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserControllerTest extends WebTestCase
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

    }

    public function testDelete()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'otherUser@libriciel']);

        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("L\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['id' => $user->getId()]));
    }

    public function testDeleteBatch()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/deleteBatch');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Suppression des élus par lot")');
        $this->assertCount(1, $item);

        $actor1 = $this->getOneUserBy(['username' => 'actor1@libriciel']);
        $actor2 = $this->getOneUserBy(['username' => 'actor1@libriciel']);

        $this->client->request(Request::METHOD_POST, '/user/deleteBatch', [
            'users' => [$actor1->getId(), $actor2->getId()],
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Les élus ont été supprimés")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneUserBy(['id' => $actor1->getId()]));
        $this->assertEmpty($this->getOneUserBy(['id' => $actor2->getId()]));
    }

    public function testDeleteOtherStructureUser()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();
        /** @var User $user */
        $user = $this->getOneEntityBy(User::class, ['username' => 'user@montpellier']);
        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteMyself()
    {
        UserStory::load();
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
        ConfigurationStory::load();
        UserStory::load();
        $adminRole = RoleStory::admin();

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

        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'newuser@libriciel']));
    }

    //# A verifier ##
    public function testEdit()
    {
        ConfigurationStory::load();
        $user = UserStory::otherUserLibriciel();
        $this->loginAsAdminLibriciel();
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
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testEditSecretary()
    {
        $user = UserStory::secretaryLibriciel1();
        ConfigurationStory::load();
        $type = TypeStory::typeBureauLibriciel();
        $this->loginAsAdminLibriciel();

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
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $user->refresh();
        $this->assertSame($user->getAuthorizedTypes()->first()->getId(), $type->getId());
    }

    public function testEditSecretaryRemoveAllAuthorized()
    {
        $user = UserStory::secretaryLibriciel1();
        ConfigurationStory::load();
        $type = TypeStory::typeBureauLibriciel();
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['user[authorizedTypes]'] = [];
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $user->refresh();
        $this->assertCount(0, $user->getAuthorizedTypes());
    }

    public function testIndex()
    {
        UserStory::load();
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/user');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Utilisateurs")');
        $this->assertCount(1, $item);
    }

    public function testPreferences()
    {
        SubscriptionStory::load();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/preferences');
        $this->assertResponseStatusCodeSame(200);

        $this->assertSelectorTextSame('h1', 'Préférences utilisateur');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['user_preference[email]'] = 'NewEmail@exameple.org';
        $form['user_preference[subscription][acceptMailRecap]'] = true;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Vos préférences utilisateur ont bien été modifiées")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($user = $this->getOneEntityBy(User::class, [
            'email' => 'NewEmail@exameple.org',
        ]));

        $subscription = $this->getOneEntityBy(Subscription::class, [
            'user' => $user->getId(),
            'acceptMailRecap' => true,
        ]);
        $this->assertNotEmpty($subscription);
        $this->assertNotNull($subscription->getCreatedAt());
    }

    public function testInvalidateUsersPassword()
    {
        ConfigurationStory::load();
        UserStory::load();
        $libriciel = StructureStory::libriciel();
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user');
        $this->assertResponseStatusCodeSame(200);
        $page = $crawler->filter('html:contains("Utilisateurs")');
        $this->assertCount(1, $page);

        $crawler->selectButton('Invalider')->form();

        $this->client->request(Request::METHOD_POST, '/user/invalidatePassword');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Tous les mots de passe ont été invalidés")');
        $this->assertCount(1, $successMsg);

        $userRepository = self::getContainer()->get(UserRepository::class);
        $users = $userRepository->findBy(['structure' => $libriciel->object()]);

        $this->assertSame($users[0]->getPassword(), PasswordInvalidator::INVALID_PASSWORD);
    }
}
