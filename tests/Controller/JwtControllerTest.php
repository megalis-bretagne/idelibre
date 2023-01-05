<?php

namespace App\Tests\Controller;

use App\Controller\JwtController;
use App\Entity\User;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\PartyStory;
use App\Tests\Story\RoleStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class JwtControllerTest extends WebTestCase
{

    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        UserStory::load();
    }


    public function testInvalidateBeforeNow()
    {
        $this->loginAsAdminLibriciel();

        $user = UserFactory::createOne([
            'role' => RoleStory::actor(),
            'structure' => StructureStory::libriciel()
        ]);

        $this->client->request(Request::METHOD_POST, '/jwt/invalidate/' . $user->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $successMsg = $crawler->filter('html:contains("Toutes les connexions actives de l\'élu ont été supprimées")');
        $this->assertCount(1, $successMsg);

        $this-> assertNotNull($user->getJwtInvalidBefore());
    }


}
