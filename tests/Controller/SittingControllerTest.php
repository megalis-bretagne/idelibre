<?php

namespace App\Tests\Controller;

use App\Controller\SittingController;
use App\DataFixtures\SittingFixtures;
use App\Entity\Sitting;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SittingControllerTest extends WebTestCase
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
            SittingFixtures::class
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
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Seances")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $type = $this->getOneTypeBy(['name' => 'unUsedType']);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/sitting/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter une séance")');
        $this->assertCount(1, $item);

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../resources/fichier.pdf', __DIR__ . '/../resources/convocation.pdf');

        $fileConvocation = new UploadedFile(__DIR__ . '/../resources/convocation.pdf', 'fichier.pdf', 'application/pdf');

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['sitting[type]'] = $type->getId();
        $form['sitting[date]'] = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $form['sitting[place]'] = 'place';
        $form['sitting[convocationFile]'] = $fileConvocation;


        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Gérer les destinataires")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Sitting::class, ['name' => 'unUsedType']));
    }



}
