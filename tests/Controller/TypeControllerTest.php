<?php

namespace App\Tests\Controller;

use App\Entity\Type;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TypeControllerTest extends WebTestCase
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
        TypeStory::load();
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
        $item = $crawler->filter('html:contains("Ajout d\'un Type de séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter le type')->form();

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
        $actorLs = UserStory::actorLibriciel1();

        $guestLs = UserFactory::createOne([
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::guest(),
        ]);

        $employeeLs = UserFactory::createOne([
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::employee(),
        ]);


        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/type/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'un Type de séance")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter le type')->form();

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
        $type = TypeStory::typeConseilLibriciel();
        $notAssociatedActor = UserStory::actorLibriciel3();

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

        $notAssociatedActor->refresh();

        $modifiedType = $this->getOneTypeBy(['name' => 'new name']);
        $this->assertNotEmpty($modifiedType);
        $this->assertCount(1, $modifiedType->getAssociatedUsers());
        $this->assertSame($notAssociatedActor->object(), $modifiedType->getAssociatedUsers()->first());
    }
}
