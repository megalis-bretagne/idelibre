<?php

namespace App\Tests\Controller\WebService;

use App\DataFixtures\RoleFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Theme;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class LegacyWsControllerTest extends WebTestCase
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
            StructureFixtures::class,
            UserFixtures::class,
            RoleFixtures::class,
            ThemeFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testAddSitting()
    {
        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/project3.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1.pdf');
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex2.pdf');

        $fileConvocation = new UploadedFile(__DIR__ . '/../../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');
        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/project1.pdf', 'project1.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'project2.pdf', 'application/pdf');
        $fileProject3 = new UploadedFile(__DIR__ . '/../../resources/project3.pdf', 'project3.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(__DIR__ . '/../../resources/annex1.pdf', 'annex1.pdf', 'application/pdf');
        $fileAnnex2 = new UploadedFile(__DIR__ . '/../../resources/annex2.pdf', 'annex2.pdf', 'application/pdf');

        $username = 'secretary1';
        $password = 'password';
        $conn = 'libriciel';

        $sittingData = [
            'place' => '8 rue de la Mairie',
            'type_seance' => 'Commission webservice',
            'date_seance' => '2021-05-12 09:30',
            'acteurs_convoques' => '[
            {"Acteur":{"nom":"DURAND","prenom":"Thomas","salutation":"Monsieur","titre":"Pr\\u00e9sident","email":"thomas.durand@example.org","telmobile":""}},
            {"Acteur":{"nom":"DUPONT","prenom":"Emilie","salutation":"Madame","titre":"1ERE Vice-President","email":"emilie.dupont@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTINEZ","prenom":"Franck","salutation":"Monsieur","titre":"","email":"frank.martinez@gmail.com","telmobile":""}},
            {"Acteur":{"nom":"POMMIER","prenom":"Sarah","salutation":"Madame","titre":"","email":"sarah.pommier@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTIN","prenom":"Philippe","salutation":"Monsieur","titre":"","email":"philippe.marton@example.org","telmobile":""}}
            ]',
            'projets' => [
                [
                    'ordre' => 0,
                    'libelle' => 'tarif cimetiere1',
                    'theme' => 'T1, STA',
                ],
                [
                    'ordre' => 1,
                    'libelle' => 'tarif cimetiere2',
                    'theme' => 'T1, STB , sstb',
                    'annexes' => [['ordre' => 0], ['ordre' => 1]],
                ],
                [
                    'ordre' => 2,
                    'libelle' => 'tarif cimetiere3',
                    'theme' => 'STA, ssta',
                    'Rapporteur' => ['rapporteurlastname' => 'DURAND', 'rapporteurfirstname' => 'Thomas'],
                ],
            ],
        ];

        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => $username,
                'password' => $password,
                'conn' => $conn,
                'jsonData' => json_encode($sittingData),
            ],
            [
                'convocation' => $fileConvocation,
                'projet_0_rapport' => $fileProject1,
                'projet_1_rapport' => $fileProject2,
                'projet_1_0_annexe' => $fileAnnex1,
                'projet_1_1_annexe' => $fileAnnex2,
                'projet_2_rapport' => $fileProject3,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue($response->success);
        $this->assertSame('Seance.add.ok', $response->code);
        $this->assertSame('La séance a bien été ajoutée.', $response->message);
        $this->assertNotEmpty($response->uuid);

        $sitting = $this->getOneSittingBy(['id' => $response->uuid]);
        $this->assertCount(3, $sitting->getProjects());
        $this->assertCount(5, $sitting->getConvocations());
        $this->assertSame('2021-05-12 07:30', $sitting->getDate()->format('Y-m-d H:i'));
        $this->assertCount(2, $sitting->getProjects()[1]->getAnnexes());

        $this->assertSame('t.durand@libriciel', $sitting->getProjects()[2]->getReporter()->getUsername());

        $user = $this->getOneUserBy(['username' => 't.durand@libriciel']);
        $this->assertNotNull($user);

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $this->assertCount(1, $themeRepository->findBy(['name' => 'T1' ]));


    }


    public function testAddSittingNoProjects()
    {
        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../../resources/fichier.pdf', __DIR__ . '/../../resources/convocation.pdf');

        $fileConvocation = new UploadedFile(__DIR__ . '/../../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');

        $username = 'secretary1';
        $password = 'password';
        $conn = 'libriciel';

        $sittingData = [
            'place' => '8 rue de la Mairie',
            'type_seance' => 'Commission webservice',
            'date_seance' => '2021-05-12 09:30',
            'acteurs_convoques' => '[
            {"Acteur":{"nom":"DURAND","prenom":"Thomas","salutation":"Monsieur","titre":"Pr\\u00e9sident","email":"thomas.durand@example.org","telmobile":""}},
            {"Acteur":{"nom":"DUPONT","prenom":"Emilie","salutation":"Madame","titre":"1ERE Vice-President","email":"emilie.dupont@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTINEZ","prenom":"Franck","salutation":"Monsieur","titre":"","email":"frank.martinez@gmail.com","telmobile":""}},
            {"Acteur":{"nom":"POMMIER","prenom":"Sarah","salutation":"Madame","titre":"","email":"sarah.pommier@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTIN","prenom":"Philippe","salutation":"Monsieur","titre":"","email":"philippe.marton@example.org","telmobile":""}}
            ]',
            'projets' => null,
        ];

        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => $username,
                'password' => $password,
                'conn' => $conn,
                'jsonData' => json_encode($sittingData),
            ],
            [
                'convocation' => $fileConvocation,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertTrue($response->success);
        $this->assertSame('Seance.add.ok', $response->code);
        $this->assertSame('La séance a bien été ajoutée.', $response->message);
        $this->assertNotEmpty($response->uuid);
    }

    public function testAddSittingNoConn()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => 'secretary1',
                'password' => 'password',
                'jsonData' => json_encode(['a' => 1]),
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('fields jsonData, username, password and conn must be set', $content->message);
    }

    public function testAddSittingNotExistConnection()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => 'secretary1',
                'password' => 'password',
                'conn' => 'notExists',
                'jsonData' => json_encode(['a' => 1]),
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('connection does not exist', $content->message);
    }

    public function testAddSittingNotExistUser()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => 'notExists',
                'password' => 'password',
                'conn' => 'libriciel',
                'jsonData' => json_encode(['a' => 1]),
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('Authentication error', $content->message);
    }

    public function testAddSittingWrongPassword()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => 'secretary1',
                'password' => 'wrongPassword',
                'conn' => 'libriciel',
                'jsonData' => json_encode(['a' => 1]),
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('Authentication error', $content->message);
    }

    public function testAddSittingWrongFormatJsonData()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/seances.json',
            [
                'username' => 'secretary1',
                'password' => 'password',
                'conn' => 'libriciel',
                'jsonData' => json_encode(['a' => 1]),
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('date_seance is required', $content->message);
    }

    public function testPing()
    {
        $this->client->request(Request::METHOD_GET, '/api300/ping');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('ping', $this->client->getResponse()->getContent());
    }

    public function testPingCapitalUrl()
    {
        $this->client->request(Request::METHOD_GET, '/Api300/ping');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('ping', $this->client->getResponse()->getContent());
    }

    public function testVersion()
    {
        $this->client->request(Request::METHOD_GET, '/api300/version');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('4.0.3', $this->client->getResponse()->getContent());
    }

    public function testVersionCapitalUrl()
    {
        $this->client->request(Request::METHOD_GET, '/Api300/version');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('4.0.3', $this->client->getResponse()->getContent());
    }

    public function testCheck()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api300/check',
            [
                'username' => 'secretary1',
                'password' => 'password',
                'conn' => 'libriciel',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('success', $this->client->getResponse()->getContent());
    }

    public function testCheckWrongConn()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api300/check',
            [
                'username' => 'secretary1',
                'password' => 'password',
                'conn' => 'falseConn',
            ]
        );

        $this->assertResponseStatusCodeSame(403);
        $this->assertSame('connection does not exist', $this->client->getResponse()->getContent());
    }

    public function testCheckWrongPassword()
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api300/check',
            [
                'username' => 'secretary1',
                'password' => 'passwordFalse',
                'conn' => 'libriciel',
            ]
        );

        $this->assertResponseStatusCodeSame(403);
        $this->assertSame('Authentication error', $this->client->getResponse()->getContent());
    }
}
