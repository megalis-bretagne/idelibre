<?php

namespace App\Tests\Controller\WebService;

use App\DataFixtures\RoleFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class LegacyWsControllerTest extends WebTestCase
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
            StructureFixtures::class,
            UserFixtures::class,
            RoleFixtures::class
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

        $fileConvocation = new UploadedFile(__DIR__ . '/../../resources/convocation.pdf', 'convocation.pdf', 'application/pdf');
        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/project1.pdf', 'project1.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'project2.pdf', 'application/pdf');
        $fileProject3 = new UploadedFile(__DIR__ . '/../../resources/project3.pdf', 'project3.pdf', 'application/pdf');

        $username = 'secretary1';
        $password = 'password';
        $conn = 'libriciel';

        $sittingData = [
            'place' => '8 rue de la Mairie',
            'type_seance' => 'Commission webservice',
            'date_seance' => date('Y-m-d H:i'),
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
                    'theme' => 'T1, STB , sstb'
                ],
                [
                    'ordre' => 2,
                    'libelle' => 'tarif cimetiere3',
                    'theme' => 'STA, ssta',
                    'Rapporteur' => ['rapporteurlastname' => 'DURAND', 'rapporteurfirstname' => 'Thomas']
                ],
            ]
        ];


        $res = $this->client->request(
            Request::METHOD_POST,
            '/seance.json' ,
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
                'projet_2_rapport' => $fileProject3,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        dd('done');

    }


}
