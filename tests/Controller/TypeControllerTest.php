<?php

namespace App\Tests\Controller;

use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Type;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TypeControllerTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManagerInterface
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
            TypeFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->getConnection()->close();
    }


    public function testIndex(){
        $this->loginAsAdminLibriciel();
        $crawler = $this->client->request(Request::METHOD_GET, '/type');
        $this->assertResponseStatusCodeSame(200);
        $title = $crawler->filter('html:contains("Types de séance")');
        $this->assertCount(1, $title);
    }

    public function testDelete()
    {
        $this->loginAsAdminLibriciel();
        /** @var Type $user */
        $type = $this->getOneEntityBy(Type::class, ['name' => 'Conseil Communautaire Libriciel']);
        $this->client->request(Request::METHOD_DELETE, '/type/delete/' . $type->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);
        $successMsg = $crawler->filter('html:contains("Le type a bien été supprimé")');
        $this->assertCount(1, $successMsg);
    }


    public function testDeleteNotMyType()
    {
        $this->loginAsAdminLibriciel();
        /** @var Type $user */
        $type = $this->getOneEntityBy(Type::class, ['name' => 'Conseil Municipal Montpellier']);
        $this->client->request(Request::METHOD_DELETE, '/type/delete/' . $type->getId());
        $this->assertResponseStatusCodeSame(403);
    }





}
