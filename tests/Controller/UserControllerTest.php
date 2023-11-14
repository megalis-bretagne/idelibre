<?php

namespace App\Tests\Controller;

use App\Entity\EventLog\Action;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\PasswordInvalidator;
use App\Tests\Factory\GroupFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConfigurationStory;
use App\Tests\Story\GroupStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\SubscriptionStory;
use App\Tests\Story\TimezoneStory;
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
        $user = UserFactory::createOne(['structure' => StructureStory::libriciel()])->object();

        $this->loginAsAdminLibriciel();
        $userId = $user->getId();

        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("L\'utilisateur a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($user->getId());

        $logEvent = $this->getOneEventLog(["targetId" => $userId, "action" => Action::USER_DELETE]);
        $this->assertNotEmpty($logEvent);
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
        $user = UserFactory::createOne();

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_DELETE, '/user/delete/' . $user->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteMyself()
    {
        $user = UserStory::adminLibriciel();

        $this->loginAsAdminLibriciel();

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
        $roleAdmin = RoleStory::admin();

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/user/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'un utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter l\'utilisateur')->form();

        $form['user[firstName]'] = 'new';
        $form['user[lastName]'] = 'user';
        $form['user[username]'] = 'newuser';
        $form['user[email]'] = 'newuser@example.org';
        $form['user[role]'] = $roleAdmin->getId();
        $form['user[plainPassword][first]'] = 'password';
        $form['user[plainPassword][second]'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $newUser = $this->getOneUserBy(['username' => 'newuser@libriciel']);
        $this->assertNotEmpty($newUser);

        $logEvent = $this->getOneEventLog(["targetId" => $newUser->getId()]);
        $this->assertNotEmpty($logEvent);
        $this->assertEquals(Action::USER_CREATE, $logEvent->getAction());
    }

    //# A verifier ##
    public function testChangePassword()
    {
        ConfigurationStory::load();
        UserStory::load();
        $user = UserFactory::createOne(['structure' => StructureStory::libriciel()]);

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification de l\'utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['user[initPassword]'] = true;
        $form['user[plainPassword][first]'] = 'OoL3chaere6axuteeR2a';
        $form['user[plainPassword][second]'] = 'OoL3chaere6axuteeR2a';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $logEvent = $this->getOneEventLog(["targetId" => $user->getId(), "action" => Action::USER_PASSWORD_UPDATED]);
        $this->assertNotEmpty($logEvent);
    }


    public function testEdit()
    {
        ConfigurationStory::load();
        UserStory::load();
        $user = UserFactory::createOne(['structure' => StructureStory::libriciel()]);

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification de l\'utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['user[initPassword]'] = 0;
        $form['user[firstName]'] = 'new';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $logEvent = $this->getOneEventLog(["targetId" => $user->getId(), "action" => Action::USER_PASSWORD_UPDATED]);
        $this->assertEmpty($logEvent);
    }

    public function testEditSecretary()
    {
        ConfigurationStory::load();
        $user = UserStory::secretaryLibriciel1();
        $type = TypeStory::typeConseilLibriciel();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification de l\'utilisateur")');
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
        $item = $crawler->filter('html:contains("Modification de l\'utilisateur")');
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
        UserStory::load();

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
        UserStory::load();
        ConfigurationStory::load();
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

        $logEvent = $this->getOneEventLog(["targetId" => $users[0]->getId(), "action" => Action::USER_PASSWORD_UPDATED]);
        $this->assertNotEmpty($logEvent);
    }

    public function testEditAdmin()
    {
        ConfigurationStory::load();
        $user = UserStory::adminLibriciel();

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());

        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification de l\'utilisateur")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['user[firstName]'] = 'new';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre utilisateur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $user->refresh();
    }

    public function testInvalidateUserPasswordAsGroupAdmin() {

        GroupStory::organisation();

        $structure =  StructureFactory::new([
            'name' => 'Lib',
            'suffix' => 'lib',
            'legacyConnectionName' => 'lib',
            'replyTo' => 'lib@exemple.org',
            'timezone' => TimezoneStory::paris(),
            'group' => GroupStory::organisation(),
            'canEditReplyTo' => true,
        ])->create()->object();

        UserFactory::new([
            'username' => 'groupAdmin',
            'email' => 'groupAdmin@example.org',
            'firstName' => 'group',
            'lastName' => 'admin',
            'group' => GroupStory::organisation(),
            'role' => RoleStory::groupadmin(),
            'structure' => $structure
        ])->create()->object();

        $actor = UserFactory::createOne(['structure' => $structure]);

        $this->loginAsGroupAdmin();

        $this->client->request(Request::METHOD_POST, '/user_invalidate_password/' . $actor->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Un e-mail de réinitialisation du mot de passe a été envoyé")');
        $this->assertCount(1, $successMsg);
        $this->assertSame(PasswordInvalidator::INVALID_PASSWORD, $actor->getPassword());
    }

    public function testInvalidateUserPasswordAsStructureAdmin() {

        $structure = StructureStory::libriciel();
        UserStory::adminLibriciel();

        $actor = UserFactory::createOne(['structure' => $structure]);

        $this->loginAsAdminLibriciel();

        $this->client->request(Request::METHOD_POST, '/user_invalidate_password/' . $actor->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Un e-mail de réinitialisation du mot de passe a été envoyé")');
        $this->assertCount(1, $successMsg);
        $this->assertSame(PasswordInvalidator::INVALID_PASSWORD, $actor->getPassword());
    }

    public function testInvalidateUserPasswordNotAdminRole() {

        UserStory::secretaryLibriciel1();
        $actor = UserFactory::createOne(['structure' => StructureStory::libriciel()]);

        $this->loginAsSecretaryLibriciel();

        $this->client->request(Request::METHOD_POST, '/user_invalidate_password/' . $actor->getId());
        $this->assertResponseStatusCodeSame(403);
    }

}


