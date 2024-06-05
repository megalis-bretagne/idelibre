<?php

namespace App\Tests\Controller;

use App\Entity\Structure;
use App\Entity\Timezone;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

// TODO improve TESTS STRUCTURE CREATION (ie connectors, templates ...)

class StructureControllerTest extends WebTestCase
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

    public function testIndex()
    {
        $this->loginAsSuperAdmin();
        $this->client->request(Request::METHOD_GET, '/structure');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Structures');
    }

    public function testIndexNotAuth()
    {
        $this->client->request(Request::METHOD_GET, '/structure');
        $this->assertResponseStatusCodeSame(302);
    }

    public function testAdd()
    {
        /** @var Timezone $timezone */
        $timezone = $this->getOneEntityBy(Timezone::class, []);

        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/structure/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajout d\'une structure');

        $form = $crawler->selectButton('Ajouter la structure')->form();

        $form['structure[name]'] = 'New structure';
        $form['structure[timezone]'] = $timezone->getId();
        $form['structure[suffix]'] = 'newStr';
        $form['structure[replyTo]'] = 'newStructure@example.org';
        $form['structure[can_edit_reply_to]'] = true;
        $form['structure[user][username]'] = 'new user';
        $form['structure[user][firstName]'] = 'prenom';
        $form['structure[user][lastName]'] = 'nom';
        $form['structure[user][email]'] = 'email@email.fr';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("La structure a été créée")');
        $this->assertCount(1, $successMsg);

        $newStructure = $this->getOneStructureBy(['name' => 'New structure']);

        $this->assertNotEmpty($newStructure);

        $this->assertNotEmpty($newStructure->getConfiguration());
        $this->assertSame(true, $newStructure->getConfiguration()->getIsSharedAnnotation());
    }

    public function testAddGroupAdmin()
    {
        $this->login('userGroupRecia');

        $this->client->request(Request::METHOD_GET, '/structure/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajout d\'une structure');
    }

    public function testAddGroupAdminNotStructureCreator()
    {
        $adminNotStructureCreator = UserFactory::createOne([
            'role' => RoleStory::groupadmin(),
            'group' => GroupStory::notStructureCreator()
        ])->object();

        $this->login($adminNotStructureCreator->getUsername());
        $this->client->request(Request::METHOD_GET, '/structure/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEdit()
    {
        $structure = StructureStory::libriciel();

        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/structure/edit/' . $structure->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $item = $crawler->filter('html:contains("Modification de la structure ")');
        $this->assertCount(1, $item);
        $form = $crawler->selectButton('Enregistrer')->form();

        $form['structure[name]'] = 'New structure name';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("La structure a été modifiée")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Structure::class, [
            'name' => 'New structure name',
        ]));
    }

    public function testDelete()
    {
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_DELETE, '/structure/delete/' . $structure->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("La structure a bien été supprimée")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Structure::class, [
            'id' => $structure->getId(),
        ]));
    }

    public function testPreferences()
    {
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/structure/preferences');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Informations de la structure');

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['structure_information[name]'] = 'New structure name';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Les informations de la structure ont été mises à jour")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Structure::class, [
            'name' => 'New structure name',
        ]));
    }
}
