<?php

namespace App\Tests\Controller\Csv;

use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TimezoneStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ExportCsvUserControllerTest extends WebTestCase
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
     }

     public function testExportUserFromStructure()
     {
         UserStory::load();
         $structure = StructureStory::libriciel();
         $this->loginAsAdminLibriciel();

         $this->client->request(Request::METHOD_GET, '/export/csv/structure/users');
         $this->assertResponseStatusCodeSame(200);
         $response = $this->client->getResponse();
         $this->assertTrue($response->headers->has('content-disposition'));
         $this->assertSame('attachment; filename=' . $structure->getName() . '.csv', $response->headers->get('content-disposition'));
         $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));
         $this->assertGreaterThan(20, intval($response->headers->get('content-length')));
     }

     public function testExportUserFromGroup()
     {
         $group = GroupStory::organisation()->object();

         $structure =  StructureFactory::new([
             'name' => 'Lib',
             'suffix' => 'lib',
             'legacyConnectionName' => 'lib',
             'replyTo' => 'lib@exemple.org',
             'timezone' => TimezoneStory::paris(),
             'group' => GroupStory::organisation(),
             'canEditReplyTo' => true,
         ])->create()->object();

         UserFactory::new([
             'username' => 'groupAdmin',
             'email' => 'groupAdmin@example.org',
             'firstName' => 'group',
             'lastName' => 'admin',
             'group' => GroupStory::organisation(),
             'role' => RoleStory::groupadmin(),
             'structure' => $structure
         ])->create()->object();

         $this->loginAsGroupAdmin();

         $this->client->request(Request::METHOD_GET, '/export/csv/group/' . $group->getId() . '/users');
         $this->assertResponseStatusCodeSame(200);
         $response = $this->client->getResponse();
         $this->assertTrue($response->headers->has('content-disposition')); $this->assertSame('attachment; filename=' . $group->getName() . '.zip', $response->headers->get('content-disposition'));
         $this->assertSame('application/zip', $response->headers->get('content-type'));
         $this->assertGreaterThan(20, intval($response->headers->get('content-length')));
     }
}
