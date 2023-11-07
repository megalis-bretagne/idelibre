<?php

namespace App\Tests\Controller;

use App\Entity\EmailTemplate;
use App\Entity\Type;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmailTemplateControllerTest extends WebTestCase
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
        EmailTemplateStory::load();
        TypeStory::load();
    }

    public function testIndex()
    {
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/emailTemplate');
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Modèles d\'email")');
        $this->assertCount(1, $item);
    }

    public function testAdd()
    {
        $typeBureau = TypeStory::typeBureauLibriciel();
        $this->loginAsAdminLibriciel();

        $crawler = $this->client->request(Request::METHOD_GET, '/emailTemplate/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'un modèle d\'email")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Ajouter le modèle d\'email')->form();
        $form['email_template[name]'] = 'New template';
        $form['email_template[subject]'] = 'New subject';
        $form['email_template[content]'] = 'Content';
        $form['email_template[type]'] = $typeBureau->getId();

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Votre modèle d\'email a été enregistré")');
        $this->assertCount(1, $successMsg);

        /** @var EmailTemplate $added */
        $added = $this->getOneEntityBy(EmailTemplate::class, ['name' => 'New template']);

        $this->assertNotEmpty($added);

        $this->assertSame($added->getType()->getName(), $typeBureau->getName());
    }

    public function testAddNonAuthorizedType()
    {
        $this->expectException(InvalidArgumentException::class);

        /** @var Type $typeConseil */
        $typeConseil = $this->getOneEntityBy(Type::class, ['name' => 'Conseil Communautaire Libriciel']);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/emailTemplate/add');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Ajout d\'un modèle d\'email")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['email_template[name]'] = 'New template';
        $form['email_template[content]'] = 'Content';
        $form['email_template[type]'] = $typeConseil->getId();
        $this->client->submit($form);
    }

    public function testEdit()
    {
        $this->loginAsAdminLibriciel();
        $emailTemplate = EmailTemplateStory::emailTemplateConseilLs();

        $crawler = $this->client->request(Request::METHOD_GET, '/emailTemplate/edit/' . $emailTemplate->getId());
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Modifier un modèle d\'email")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Enregistrer')->form();

        $form['email_template[name]'] = 'New name';
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Votre modèle d\'email a été modifié")');
        $this->assertCount(1, $successMsg);

        $this->assertNotEmpty($this->getOneEntityBy(EmailTemplate::class, ['name' => 'New name']));
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $this->getOneEntityBy(EmailTemplate::class, ['name' => 'Conseil Libriciel']);
        $associatedTypeId = $emailTemplate->getType()->getId();

        $this->client->request(Request::METHOD_DELETE, '/emailTemplate/delete/' . $emailTemplate->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le modèle d\'email a bien été supprimé")');
        $this->assertCount(1, $successMsg);

        $this->assertEmpty($this->getOneEntityBy(EmailTemplate::class, ['id' => $emailTemplate->getId()]));
        $this->assertNotEmpty($this->getOneEntityBy(Type::class, ['id' => $associatedTypeId]));
    }

    public function testPreviewTemplate()
    {
        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $this->getOneEntityBy(EmailTemplate::class, ['name' => 'Conseil Libriciel']);
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/emailTemplate/preview/' . $emailTemplate->getId());
        $this->assertResponseStatusCodeSame(200);

        $item = $crawler->filter('html:contains("Prévisualisation de l\'email")');
        $this->assertCount(1, $item);
    }
}
