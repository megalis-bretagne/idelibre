<?php

namespace App\Tests\Controller;

use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Type;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TypeControllerTest extends WebTestCase
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
            TypeFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->getConnection()->close();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/type');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Types de séance")');
        $this->assertCount(1, $title);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);

        $this->client->request(Request::METHOD_DELETE, '/type/delete/' . $type->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le type a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Type::class, ['id' => $type->getId()]));
    }

    public function testDeleteNotMyType()
    {
        $this->loginAsAdminLibriciel();
        $type = $this->getOneTypeBy(['name' => 'Conseil Municipal Montpellier']);
        $this->client->request(Request::METHOD_DELETE, '/type/delete/' . $type->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdd()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/type/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un Type de séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['type[name]'] = 'New type';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre type a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Type::class, ['name' => 'New type']));
    }

    public function testAddWithAssociatedUsers()
    {
        $actorLs = $this->getOneUserBy(['username' => 'actor1@libriciel.coop']);
        $guestLs = $this->getOneUserBy(['username' => 'guest2@libriciel.coop']);
        $employeeLs = $this->getOneUserBy(['username' => 'employee2@libriciel.coop']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/type/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un Type de séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['type[name]'] = 'New type';
        $form['type[associatedActors]'] = $actorLs->getId();
        $form['type[associatedEmployees]'] = $employeeLs->getId();
        $form['type[associatedGuests]'] = $guestLs->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre type a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $addedType = $this->getOneTypeBy(['name' => 'New type']);
        $this->assertNotEmpty($addedType);

        $this->assertCount(3, $addedType->getAssociatedUsers());
    }

    public function testEdit()
    {
        $this->loginAsAdminLibriciel();
        $type = $this->getOneTypeBy(['name' => 'Conseil Communautaire Libriciel']);
        $notAssociatedActor = $this->getOneUserBy(['username' => 'actor3@libriciel.coop']);
        $crawler = $this->client->request(Request::METHOD_GET, '/type/edit/' . $type->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un type de séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['type[name]'] = 'new name';
        $form['type[associatedActors]'] = $notAssociatedActor->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre type a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $modifiedType = $this->getOneTypeBy(['name' => 'new name']);
        $this->assertNotEmpty($modifiedType);
        $this->assertCount(1, $modifiedType->getAssociatedUsers());
        $this->assertSame($notAssociatedActor, $modifiedType->getAssociatedUsers()->first());
    }
}
