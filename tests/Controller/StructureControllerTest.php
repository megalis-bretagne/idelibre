<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Structure;
use App\Entity\Timezone;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

// TODO improve TESTS STRUCTURE CREATION (ie connectors, templates ...)

class StructureControllerTest extends WebTestCase
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
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testIndex()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/structure');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Structures")');
        $this->assertCount(1, $item);
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
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter une structure")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['structure[name]'] = 'New structure';
        $form['structure[timezone]'] = $timezone->getId();
        $form['structure[suffix]'] = 'newStr';
        $form['structure[replyTo]'] = 'newStructure@example.org';
        $form['structure[user][username]'] = 'new user';
        $form['structure[user][firstName]'] = 'prenom';
        $form['structure[user][lastName]'] = 'nom';
        $form['structure[user][email]'] = 'email@email.fr';
        $form['structure[user][plainPassword][first]'] = 'password';
        $form['structure[user][plainPassword][second]'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La structure a été créée")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Structure::class, ['name' => 'New structure']));
    }

    public function testEdit()
    {
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/structure/edit/' . $structure->getId());
        $this->assertResponseStatusCodeSame(200);

        $editTitle = $crawler->filter('html:contains("Modifier une structure")');
        $this->assertCount(1, $editTitle);
        $form = $crawler->selectButton('Enregistrer')->form();

        $form['structure[name]'] = 'New structure name';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La structure a été modifiée")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Structure::class, ['name' => 'New structure name']));
    }

    public function testDelete()
    {
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);
        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_DELETE, '/structure/delete/' . $structure->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("La structure a bien été supprimée")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Structure::class, ['id' => $structure->getId()]));
    }

    public function testPreferences()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/structure/preferences');
        $this->assertResponseStatusCodeSame(200);

        $editTitle = $crawler->filter('html:contains("Informations de la struture")');
        $this->assertCount(1, $editTitle);
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['structure_information[name]'] = 'New structure name';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Les informations de la structure ont été mises à jour")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Structure::class, ['name' => 'New structure name']));
    }
}
