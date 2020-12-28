<?php

namespace App\Tests\Controller\Connector;

use App\DataFixtures\ComelusConnectorFixtures;
use App\DataFixtures\LsMessageConnectorFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Connector\LsmessageConnector;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LsmessageConnectorControllerTest extends WebTestCase
{
    use FixturesTrait;
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

        $this->loadFixtures([
            UserFixtures::class,
            ComelusConnectorFixtures::class,
            LsMessageConnectorFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testEdit()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/connector/lsmessage');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Paramétrer le connnecteur Lsmessage")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['lsmessage_connector[apiKey]'] = 'new api key';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le connecteur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        /** @var LsmessageConnector $lsmessageConnector */
        $lsmessageConnector = $this->getOneEntityBy(LsmessageConnector::class, ['structure' => $structure]);
        $this->assertSame('new api key', $lsmessageConnector->getApiKey());
    }
}
