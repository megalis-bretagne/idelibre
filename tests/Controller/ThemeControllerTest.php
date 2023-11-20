<?php

namespace App\Tests\Controller;

use App\Entity\Theme;
use App\Tests\Factory\ThemeFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ThemeControllerTest extends WebTestCase
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
        StructureStory::load();
        ThemeStory::load();
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

        $item = $crawler->filter('html:contains("Thèmes")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/theme/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'un thème")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter le thème')->form();

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

    public function testEditWithParent()
    {
        $this->loginAsAdminLibriciel();
        /** @var $themeFinance Theme */
        $themeFinance = $this->getOneEntityBy(Theme::class, ['name' => 'Finance']);



        $crawler = $this->client->request(Request::METHOD_GET, '/theme/edit/' . $themeFinance->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification du thème")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['theme_with_parent[name]'] = 'Modified Theme';
        $form['theme_with_parent[parent]'] = ThemeStory::ecoleTheme()->getId();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre thème a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $modified = $this->getOneEntityBy(Theme::class, ['name' => 'Modified Theme']);
        $this->assertNotEmpty($modified);
        $this->assertSame(ThemeStory::ecoleTheme()->getId(), $modified->getParent()->getId());
    }

    public function testEditNoParent()
    {
        $this->loginAsAdminLibriciel();
        /** @var $themeFinance Theme */
        $themeFinance = $this->getOneEntityBy(Theme::class, ['name' => 'Finance']);
        $crawler = $this->client->request(Request::METHOD_GET, '/theme/edit/' . $themeFinance->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modification du thème")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['theme_with_parent[name]'] = 'Modified theme';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre thème a bien été modifié")');
        $this->assertCount(1, $successMsg);

        $modifiedTheme = $this->getOneEntityBy(Theme::class, ['name' => 'Modified theme']);
        $this->assertNotEmpty($modifiedTheme);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        $theme = ThemeFactory::createOne(['name' => 'financeToDelete', 'structure' => StructureStory::libriciel()])->object();

        $this->client->request(Request::METHOD_DELETE, '/theme/delete/' . $theme->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le thème a bien été supprimé")');
        $this->assertCount(1, $successMsg);
        $this->assertEmpty($theme->getId());
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
