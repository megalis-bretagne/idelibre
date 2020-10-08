<?php

namespace App\Tests\Controller;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\ThemeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Theme;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ThemeControllerTest extends WebTestCase
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
            StructureFixtures::class,
            ThemeFixtures::class
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
        $crawler = $this->client->request(Request::METHOD_GET, '/theme/index');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Themes")');
        $this->assertCount(1, $item);
    }


    public function testAdd()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/theme/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajouter un thème")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['theme_with_parent[name]'] = 'New Theme';

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre thème a bien été ajouté")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Theme::class, ['name' => 'New Theme']));
        $this->assertNotEmpty($this->getOneEntityBy(Theme::class, ['fullName' => 'New Theme']));
    }

    public function testEdit()
    {
        $this->loginAsAdminLibriciel();
        /** @var $themeFinance Theme */
        $themeFinance = $this->getOneEntityBy(Theme::class, ['name' => 'Finance']);
        $crawler = $this->client->request(Request::METHOD_GET, '/theme/edit/' . $themeFinance->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un thème")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['theme[name]'] = 'Change name';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre thème a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(Theme::class, ['name' => 'Change name']));
    }


    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        /** @var $themeFinance Theme */
        $themeFinance = $this->getOneEntityBy(Theme::class, ['name' => 'Finance']);
        $this->client->request(Request::METHOD_DELETE, '/theme/delete/' . $themeFinance->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le thème a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(Theme::class, ['id' => $themeFinance->getId()]));
    }

    public function testDeleteNotMyTheme()
    {
        $this->loginAsAdminLibriciel();
        /** @var $themeMtp Theme */
        $themeMtp = $this->getOneEntityBy(Theme::class, ['name' => 'Urbanisme Montpellier']);

        $this->client->request(Request::METHOD_DELETE, '/theme/delete/' . $themeMtp->getId());
        $this->assertResponseStatusCodeSame(403);
    }
}
