<?php

namespace App\Tests\Controller\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ComelusConnectorStory;
use App\Tests\Story\LsmessageConnectorStory;
use App\Tests\Story\LsvoteConnectorStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ComelusConnectorControllerTest extends WebTestCase
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
        ComelusConnectorStory::load();
        LsmessageConnectorStory::load();
        LsvoteConnectorStory::load();
        StructureStory::load();
    }

    public function testEdit()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/connector/comelus');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Paramétrer le connecteur Comélus")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['comelus_connector[apiKey]'] = 'new api key';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le connecteur a bien été modifié")');
        $this->assertCount(1, $successMsg);

        /** @var ComelusConnector $comelusConnector */
        $comelusConnector = $this->getOneEntityBy(ComelusConnector::class, ['structure' => $structure]);
        $this->assertSame('new api key', $comelusConnector->getApiKey());
    }
}
