<?php

namespace App\Tests\Controller\ApiV2;

use App\Service\Connector\Lsvote\LsvoteClient;
use App\Tests\Factory\LsvoteConnectorFactory;
use App\Tests\Factory\LsvoteSittingFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Story\ApiUserStory;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LsvoteResultControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;


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

        parent::setUp();
    }


    public function testGetSittingResultsAlreadyInDatabase()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $sitting = SittingFactory::createOne([
            'name' => 'sittingTest',
            'structure' => $structure,
        ]);

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            'sitting' => $sitting,
            'lsvoteSittingId' => 'lsvotesittingUUID',
            'results' => [
                [
                    'actors' => [
                        ['fistName' => 'Laetitia'], ['lastName' => 'Dupont']
                    ],
                    'votes' => [
                        ['vote' => 'Pour'], ['vote' => 'Contre']
                    ]
                ]
            ],
        ]);


        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/vote/sitting/{$sitting->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $results = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Laetitia', $results[0]['actors'][0]['fistName']);
    }


    public function testGetSittingResultsNotAlreadyInDatabase()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $sitting = SittingFactory::createOne([
            'name' => 'sittingTest',
            'structure' => $structure,
        ]);

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            'sitting' => $sitting,
            'lsvoteSittingId' => 'lsvotesittingUUID',
            'results' => [],
        ]);

        $lsvoteConnector = LsvoteConnectorFactory::createOne([
            'structure' => $structure,
        ]);


        $mockLsvoteclient = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLsvoteclient->method('resultSitting')->willReturn([
            [
                'actors' => [
                    ['fistName' => 'Laetitia'], ['lastName' => 'Dupont']
                ],
                'votes' => [
                    ['vote' => 'Pour'], ['vote' => 'Contre']
                ]
            ]
        ]);

        $container = self::getContainer();
        $container->set(LsvoteClient::class, $mockLsvoteclient);


        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/vote/sitting/{$sitting->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $results = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Laetitia', $results[0]['actors'][0]['fistName']);
    }

    public function testGetSittingResultsNotLsvotesitting()
    {
        $structure = StructureStory::libriciel();
        $apiUser = ApiUserStory::apiAdminLibriciel();

        $sitting = SittingFactory::createOne([
            'name' => 'sittingTest',
            'structure' => $structure,
        ]);

        $this->client->request(Request::METHOD_GET, "/api/v2/structures/{$structure->getId()}/vote/sitting/{$sitting->getId()}", [], [], [
            'HTTP_X-AUTH-TOKEN' => $apiUser->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(404);

        $this->assertSame("Cette séance n'a pas de vote électronique associé", json_decode($this->client->getResponse()->getContent(), true)['message']);
    }
}
