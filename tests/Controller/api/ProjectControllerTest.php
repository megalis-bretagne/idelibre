<?php

namespace App\Tests\Controller\api;

use App\DataFixtures\SittingFixtures;
use App\DataFixtures\UserFixtures;
use App\Service\ApiEntity\AnnexApi;
use App\Service\ApiEntity\ProjectApi;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ProjectControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var object|\Symfony\Component\Serializer\Serializer|null
     */
    private $serializer;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->serializer = $kernel->getContainer()
            ->get('serializer');

        $this->loadFixtures([
            SittingFixtures::class,
            UserFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }


    public function testAdd()
    {
        $sitting = $this->getOneSittingBy(['name' => ['Conseil Libriciel']]);
        $this->loginAsAdminLibriciel();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ .'/../../resources/fichier.pdf', __DIR__ . '/../../resources/project1.pdf');
        $filesystem->copy(__DIR__ .'/../../resources/fichier.pdf', __DIR__ . '/../../resources/project2.pdf');
        $filesystem->copy(__DIR__ .'/../../resources/fichier.pdf', __DIR__ . '/../../resources/annex1.pdf');

        $fileProject1 = new UploadedFile(__DIR__ . '/../../resources/project1.pdf', 'fichier.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile(__DIR__ . '/../../resources/project2.pdf', 'fichier.pdf', 'application/pdf');
        $fileAnnex1 = new UploadedFile(__DIR__ . '/../../resources/annex1.pdf', 'fichier.pdf', 'application/pdf');


        $annex = new AnnexApi();
        $annex->setLinkedFile('annex1')
            ->setRank(0);

        $project1 = new ProjectApi();
        $project1->setName("first Project")
            ->setRank(0)
            ->setLinkedFile('project1')
            ->setAnnexes([$annex]);

        $project2 = new ProjectApi();
        $project2->setName("second project")
            ->setRank(1)
            ->setLinkedFile('project2');

        $serializedProjects = $this->serializer->serialize([$project1, $project2], 'json');

        $response = $this->client->request(Request::METHOD_POST,
            '/api/projects/' . $sitting->getId(),
            ['projects' => $serializedProjects],
            [
                'project1' => $fileProject1,
                'project2' => $fileProject2,
                'annex1' => $fileAnnex1,
            ]
        );

        dd($response);

    }

}
