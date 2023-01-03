<?php

namespace App\Tests\Controller\WebService;

use App\Entity\Theme;
use App\Entity\Type;
use App\Service\S3\S3Manager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ReminderStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LegacyWsControllerTest extends WebTestCase
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

        StructureStory::load();
        UserStory::load();
        RoleStory::load();
        ThemeStory::load();
        TypeStory::load();
        ReminderStory::load();
    }

    public function testAddSitting()
    {
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertCount(1, $themeRepository->findBy(['name' => 'T1']));

        $typeRepository = $this->entityManager->getRepository(Type::class);

        /** @var Type $type */
        $type = $typeRepository->findOneBy(['name' => 'Commission webservice']);
        $this->assertNotEmpty($type);

        $this->assertCount(5, $type->getAssociatedUsers());
    }

    public function testAddSittingExistingType()
    {
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

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
            'type_seance' => 'Conseil Communautaire Libriciel',
            'date_seance' => '2021-05-12 09:30',
            'acteurs_convoques' => '[
            {"Acteur":{"nom":"DURAND","prenom":"Thomas","salutation":"Monsieur","titre":"Pr\\u00e9sident","email":"thomas.durand@example.org","telmobile":""}},
            {"Acteur":{"nom":"DUPONT","prenom":"Emilie","salutation":"Madame","titre":"1ERE Vice-President","email":"emilie.dupont@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTINEZ","prenom":"Franck","salutation":"Monsieur","titre":"","email":"frank.martinez@gmail.com","telmobile":""}},
            {"Acteur":{"nom":"POMMIER","prenom":"Sarah","salutation":"Madame","titre":"","email":"sarah.pommier@example.org","telmobile":""}},
            {"Acteur":{"nom":"MARTIN","prenom":"Philippe","salutation":"Monsieur","titre":"","email":"philippe.marton@example.org","telmobile":""}},
            {"Acteur":{"nom":"Gille","prenom":"mauriceModify","salutation":"Monsieur","titre":"","email":"martin.gille@example.org","telmobile":""}}
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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue($response->success);
        $this->assertSame('Seance.add.ok', $response->code);
        $this->assertSame('La séance a bien été ajoutée.', $response->message);
        $this->assertNotEmpty($response->uuid);

        $sitting = $this->getOneSittingBy(['id' => $response->uuid]);
        $this->assertCount(3, $sitting->getProjects());
        $this->assertCount(8, $sitting->getConvocations());
        $this->assertSame('2021-05-12 07:30', $sitting->getDate()->format('Y-m-d H:i'));
        $this->assertCount(2, $sitting->getProjects()[1]->getAnnexes());

        $this->assertSame('t.durand@libriciel', $sitting->getProjects()[2]->getReporter()->getUsername());

        $user = $this->getOneUserBy(['username' => 't.durand@libriciel']);
        $this->assertNotNull($user);

        $themeRepository = $this->entityManager->getRepository(Theme::class);

        $this->assertCount(1, $themeRepository->findBy(['name' => 'T1']));

        $typeRepository = $this->entityManager->getRepository(Type::class);

        /** @var Type $type */
        $type = $typeRepository->findOneBy(['name' => 'Conseil Communautaire Libriciel']);
        $this->assertNotEmpty($type);

        $this->assertCount(8, $type->getAssociatedUsers());

        $this->assertNotEmpty($sitting->getReminder());
    }

    public function testAddSittingActeurs_convoquesNotJson()
    {
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

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
            'acteurs_convoques' => [
                [
                    'Acteur' => [
                        'nom' => 'MARTIN',
                        'prenom' => 'Philippe',
                        'email' => 'philippe.marton@example.org',
                    ],
                ],
                [
                    'Acteur' => [
                        'nom' => 'POMMIER',
                        'prenom' => 'Sarah',
                        'email' => 'sarah.pommier@example.org',
                    ],
                ],
                [
                    'Acteur' => [
                        'nom' => 'MARTINEZ',
                        'prenom' => 'Franck',
                        'email' => 'frank.martinez@gmail.com',
                    ],
                ],
                [
                    'Acteur' => [
                        'nom' => 'DURAND',
                        'prenom' => 'Thomas',
                        'email' => 'thomas.durand@example.org',
                    ],
                ],

                [
                    'Acteur' => [
                        'nom' => 'DUPONT',
                        'prenom' => 'Emilie',
                        'email' => 'emilie.dupont@example.org',
                    ],
                ],
            ],
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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertCount(1, $themeRepository->findBy(['name' => 'T1']));
    }

    public function testAddSittingNoProjects()
    {
        $fakeS3Manager = $this->getMockBuilder(S3Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'deleteObject'])
            ->getMock()
        ;
        $fakeS3Manager->method('addObject')->willReturn(true);
        $fakeS3Manager->method('deleteObject')->willReturn(true);
        $container = self::getContainer();
        $container->set(S3Manager::class, $fakeS3Manager);

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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertFalse($content->success);
        $this->assertSame('date_seance is required', $content->message);
    }

    public function testPing()
    {
        $this->client->request(Request::METHOD_GET, '/api300/ping');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame('ping', $this->client->getResponse()->getContent());
    }

    public function testPingCapitalUrl()
    {
        $this->client->request(Request::METHOD_GET, '/Api300/ping');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame('ping', $this->client->getResponse()->getContent());
    }

    public function testVersion()
    {
        $this->client->request(Request::METHOD_GET, '/api300/version');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertMatchesRegularExpression('/4.1.*|4.2.*/', $this->client->getResponse()->getContent());
    }

    public function testVersionCapitalUrl()
    {
        $this->client->request(Request::METHOD_GET, '/Api300/version');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertMatchesRegularExpression('/4.1.*|4.2.*/', $this->client->getResponse()->getContent());
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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSame('Authentication error', $this->client->getResponse()->getContent());
    }
}
