<?php

namespace App\Tests\Repository;

use App\Repository\ApiUserRepository;
use App\Tests\Factory\ApiRoleFactory;
use App\Tests\Factory\ApiUserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ApiUserRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ApiUserRepository $apiUserRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->apiUserRepository = self::getContainer()->get(ApiUserRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testFindByStructure()
    {
        $structure = StructureStory::libriciel()->object();
        $apiRole = ApiRoleFactory::createOne();
        ApiUserFactory::createMany(5, ['structure' => $structure, 'apiRole' => $apiRole]);

        $apiUser = $this->apiUserRepository->findByStructure($structure)->getQuery()->getResult();

        $this->assertCount(5, $apiUser);
    }
}
