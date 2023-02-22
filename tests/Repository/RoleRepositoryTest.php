<?php

namespace App\Tests\Repository;

use App\Repository\RoleRepository;
use App\Tests\Factory\RoleFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RoleRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private RoleRepository $roleRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->roleRepository = self::getContainer()->get(RoleRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testFindInStructureQueryBuilder()
    {
        RoleFactory::createMany(4, []);

        $roles = $this->roleRepository->findInStructureQueryBuilder()->getQuery()->getResult();

        $this->assertCount(4, $roles);
    }
}
