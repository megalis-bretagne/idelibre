<?php

namespace App\Tests\Repository;

use App\Repository\GroupRepository;
use App\Tests\Factory\GroupFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GroupRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private GroupRepository $groupRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->groupRepository = self::getContainer()->get(GroupRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testGetAll()
    {
        GroupFactory::createMany(4, []);
        $groups = $this->groupRepository->findAllQuery()->getResult();

        $this->assertCount(4, $groups);
    }
}
