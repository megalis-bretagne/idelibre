<?php

namespace App\Tests\Controller;

use App\Entity\Group;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GroupControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
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

    public function testIndex()
    {
        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_GET, '/group');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Groupes');
    }

    public function testAdd()
    {
        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/group/add');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Ajout d\'un groupe');

        $form = $crawler->selectButton('Ajouter le groupe')->form();

        $form['group[name]'] = 'new group';
        $form['group[user][firstName]'] = 'new firstname';
        $form['group[user][lastName]'] = 'new lastname';
        $form['group[user][email]'] = 'new@email.com';
        $form['group[user][username]'] = 'newUser';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Le groupe a bien été créé")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Group::class, [
            'name' => 'new group',
        ]));
    }

    public function testEdit()
    {
        $group = GroupStory::recia();

        $this->loginAsSuperAdmin();

        $crawler = $this->client->request(Request::METHOD_GET, '/group/edit/' . $group->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $title = $crawler->filter('html:contains("Modification du groupe ' . $group->getName() . '")');
        $this->assertCount(1, $title);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['group[name]'] = 'new groupe name';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Le groupe a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testManage()
    {
        $group = GroupStory::recia();

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_GET, '/group/manage/' . $group->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextSame('h1', 'Gestion des structures du groupe ' . $group->getName());
    }

    public function testDelete()
    {
        $group = $this->getOneEntityBy(Group::class, [
            'name' => 'Recia',
        ]);

        $this->loginAsSuperAdmin();

        $this->client->request(Request::METHOD_DELETE, '/group/delete/' . $group->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $successMsg = $crawler->filter('html:contains("Le groupe a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Group::class, [
            'id' => $group->getId(),
        ]));
    }
}
