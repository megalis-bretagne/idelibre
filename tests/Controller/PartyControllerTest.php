<?php

namespace App\Tests\Controller;

use App\Entity\Party;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\PartyStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PartyControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        UserStory::load();
        PartyStory::load();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/party/index');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Groupes politiques")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $actor2 = UserStory::actorLibriciel2();
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/party/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un groupe politique")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['party[name]'] = 'New party';
        $form['party[actors]'] = [$actor2->getId()];

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre groupe politique a été ajouté")');
        $this->assertCount(1, $successMsg);

        /** @var Party $party */
        $party = $this->getOneEntityBy(Party::class, ['name' => 'New party']);
        $this->assertNotEmpty($party);

        $this->assertCount(1, $party->getActors());
        $this->assertEquals($actor2->getId(), $party->getActors()->first()->getId());
    }

    public function testEdit()
    {
        $actor2 = UserStory::actorLibriciel2();
        $actor3 = UserStory::actorLibriciel3();

        $this->loginAsAdminLibriciel();

        $party = $this->getOneEntityBy(Party::class, ['name' => 'Majorité']);
        $currentUserId = $party->getActors()->first()->getId();

        $crawler = $this->client->request(Request::METHOD_GET, '/party/edit/' . $party->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un groupe politique")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['party[name]'] = 'New party name';
        $form['party[actors]'] = [$actor2->getId(), $actor3->getId()];

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre groupe politique a été modifié")');
        $this->assertCount(1, $successMsg);

        $updated = $this->getOneEntityBy(Party::class, ['name' => 'New party name']);

        $this->assertNotEmpty($updated);

        $this->assertCount(2, $updated->getActors());

        $this->assertEmpty($this->getOneEntityBy(User::class, ['id' => $currentUserId, 'party' => $party]));
    }

    public function testDeleteNotMyParty()
    {
        $this->loginAsAdminLibriciel();
        $partyMtp = PartyStory::montpellier();
        $this->client->request(Request::METHOD_DELETE, '/party/delete/' . $partyMtp->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        /** @var $party Party */
        $party = $this->getOneEntityBy(Party::class, ['name' => 'Majorité']);
        $crawler = $this->client->request(Request::METHOD_DELETE, '/party/delete/' . $party->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le groupe politique a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Party::class, ['id' => $party->getId()]));
    }
}
