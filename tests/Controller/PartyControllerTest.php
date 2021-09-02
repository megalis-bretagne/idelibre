<?php

namespace App\Tests\Controller;

use App\DataFixtures\PartyFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Party;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class PartyControllerTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            PartyFixtures::class,
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
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/party/index');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Groupes politiques")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        /** @var User $actor2 */
        $actor2 = $this->getOneEntityBy(User::class, ['username' => 'actor2@libriciel']);

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
        /** @var User $actor2 */
        $actor2 = $this->getOneEntityBy(User::class, ['username' => 'actor2@libriciel']);

        /** @var User $actor3 */
        $actor3 = $this->getOneEntityBy(User::class, ['username' => 'actor3@libriciel']);

        $this->loginAsAdminLibriciel();
        /** @var $party Party */
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
        $this->entityManager->refresh($updated);
        $this->assertNotEmpty($updated);

        $this->assertCount(2, $updated->getActors());

        $this->assertEmpty($this->getOneEntityBy(User::class, ['id' => $currentUserId, 'party' => $party]));
    }

    public function testDeleteNotMyParty()
    {
        $this->loginAsAdminLibriciel();
        $partyMtp = $this->getOneEntityBy(Party::class, ['name' => 'Montpellier']);
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
