<?php

namespace App\Tests\Controller\Csv;

use App\Entity\Type;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\RoleStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CsvUserControllerTest extends WebTestCase
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
        RoleStory::load();
        TypeStory::load();
    }

    public function testImportUsers()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_success.csv', 'user_success.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Fichier csv importé avec succès")');
        $this->assertCount(1, $successMsg);

        $user = $this->getOneUserBy(['username' => 'p.dumontet@libriciel']);
        $this->assertNotEmpty($user);
        $this->assertSame(2, $user->getGender());
        $this->assertSame('Actor', $user->getRole()->getName());
        $this->assertSame('0687896754', $user->getPhone());
        $this->assertSame('Depute', $user->getTitle());
        $this->assertCount(4, $user->getAssociatedTypes()->toArray());
        $this->assertNotEmpty($this->getOneEntityBy(Type::class, ['name' => 'New type']));

        $user2 = $this->getOneUserBy(['username' => 'P.JUSSEAU@libriciel']);
        $this->assertNotEmpty($user2);
        $this->assertSame('Admin', $user2->getRole()->getName());
        $this->assertSame("", $user2->getPhone());
        $this->assertCount(0, $user2->getAssociatedTypes()->toArray());

        $employee = $this->getOneUserBy(['username' => 'T.AUCLAIR@libriciel']);
        $this->assertNotEmpty($employee);
        $this->assertSame(2, $employee->getGender());
        $this->assertSame('0687896754', $employee->getPhone());
        $this->assertSame('senateur', $employee->getTitle());

    }

    public function testImportUsersMissingEmail()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_email_missing.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['username' => 't.martin@libriciel']));
        $this->assertEmpty($this->getOneEntityBy(Type::class, ['name' => 'New type']));

        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'e.dupont@libriciel']));
    }

    public function testImportUsersMissingUsername()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_username_missing.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);

        $this->assertEmpty($this->getOneEntityBy(User::class, ['username' => 't.martin@libriciel']));
        $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'e.dupont@libriciel']));
    }

    public function testImportUserCsvMissingField()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_missing_fields.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);

    }

    public function testImportUserCsvNoRole()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_no_role.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);

        $errorMsg = $crawler->filter('html:contains("Cette valeur ne doit pas être nulle.")');
        $this->assertCount(1, $errorMsg);
        $errorMsg = $crawler->filter('html:contains("Champ en erreur : role")');
        $this->assertCount(1, $errorMsg);
    }

    public function testCsvErrorWithNoError()
    {
        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/csv/userErrors');
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Utilisateurs")');
        $this->assertCount(1, $title);
    }

    public function testAddDeputyToActorNoErrors()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_success_deputy.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/csv/userErrors');

        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Fichier csv importé avec succès")');
        $this->assertCount(1, $successMsg);

        $user = $this->getOneUserBy(['username' => 'p.dumontet@libriciel']);
        $this->assertNotEmpty($user);
        $this->assertSame(2, $user->getGender());
        $this->assertSame('Actor', $user->getRole()->getName());
        $this->assertSame('0687896754', $user->getPhone());
        $this->assertSame('Depute', $user->getTitle());
        $this->assertSame('s.goodman@libriciel', $user->getDeputy()->getUsername());

        $deputy = $this->getOneUserBy(['username' => 's.goodman@libriciel']);
        $this->assertNotEmpty($deputy);
        $this->assertSame(1, $deputy->getGender());
        $this->assertSame('Deputy', $deputy->getRole()->getName());
        $this->assertSame('', $deputy->getPhone());

        $this->assertSame($deputy->getUsername(), $user->getDeputy()->getUsername());
    }

    public function testDeputyCsvErrorWithErrors()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_error_role_deputy.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/csv/userErrors');

        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);
        $this->assertEmpty($this->getOneEntityBy(User::class, ['username' => 'p.dumontet@libriciel']));
    }


    public function testPhoneWrongFormat()
    {
        $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_error_wrong_format_phone.csv', 'user.csv');
        $this->assertNotEmpty($csvFile);

        $this->loginAsAdminLibriciel();
        $this->client->request(Request::METHOD_GET, '/csv/userErrors');

        $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
        $this->assertResponseStatusCodeSame(200);
        $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
        $this->assertCount(1, $item);

        $form = $crawler->selectButton('Importer le csv')->form();

        $form['csv[csv]'] = $csvFile;

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $title = $crawler->filter('html:contains("Erreurs lors de l\'import")');
        $this->assertCount(1, $title);
    }

   public function testTitleToNotActor()
   {
       $csvFile = new UploadedFile(__DIR__ . '/../../resources/csv/user_error_wrong_role_title.csv', 'user.csv');
       $this->assertNotEmpty($csvFile);

       $this->loginAsAdminLibriciel();
       $this->client->request(Request::METHOD_GET, '/csv/userErrors');

       $crawler = $this->client->request(Request::METHOD_GET, '/csv/importUsers');
       $this->assertResponseStatusCodeSame(200);
       $item = $crawler->filter('html:contains("Importer des utilisateurs via csv")');
       $this->assertCount(1, $item);

       $form = $crawler->selectButton('Importer le csv')->form();

       $form['csv[csv]'] = $csvFile;

       $this->client->submit($form);

       $this->assertTrue($this->client->getResponse()->isRedirect());

       $this->client->followRedirect();
       $this->assertResponseStatusCodeSame(200);

       $this->assertNotEmpty($this->getOneEntityBy(User::class, ['username' => 'T.AUCLAIR@libriciel']));
       $user = $this->getOneEntityBy(User::class, ['username' => 'T.AUCLAIR@libriciel']);
       $this->assertSame('', $user->getTitle());

   }

}
