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
        $crawler = $this->client->request(Request::METHOD_GET, '/group');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Groupes")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $this->loginAsSuperAdmin();
        $crawler = $this->client->request(Request::METHOD_GET, '/group/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un groupe")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['group[name]'] = 'new group';
        $form['group[user][firstName]'] = 'new firstname';
        $form['group[user][lastName]'] = 'new lastname';
        $form['group[user][email]'] = 'new@email.com';
        $form['group[user][username]'] = 'newUser';
        $form['group[user][plainPassword][first]'] = 'password';
        $form['group[user][plainPassword][second]'] = 'password';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Le groupe a bien été créé")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Group::class, ['name' => 'new group']));
    }

    public function testEdit()
    {
        $this->loginAsSuperAdmin();
        $group = GroupStory::recia();
        $crawler = $this->client->request(Request::METHOD_GET, '/group/edit/' . $group->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un groupe")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['group[name]'] = 'new groupe name';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Le groupe a bien été modifié")');
        $this->assertCount(1, $successMsg);
    }

    public function testManage()
    {
        $this->loginAsSuperAdmin();
        $group = GroupStory::recia();
        $crawler = $this->client->request(Request::METHOD_GET, '/group/manage/' . $group->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Associer des structures")');
        $this->assertCount(1, $item);
    }

    public function testDelete()
    {
        $this->loginAsSuperAdmin();
        $group = $this->getOneEntityBy(Group::class, ['name' => 'Recia']);
        $this->client->request(Request::METHOD_DELETE, '/group/delete/' . $group->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le groupe a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Group::class, ['id' => $group->getId()]));
    }
}
