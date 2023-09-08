<?php

namespace App\Tests\Command;

use App\Repository\RoleRepository;
use App\Service\role\RoleManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateNewRoleDeputy extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
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

    public function testInitLsvoteConnector()
    {
        $cmdToTest = (new Application(self::$kernel))->find('initBdd:add_role');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);

        $cmdTester->assertCommandIsSuccessful();
        $role = $this->roleRepository->findOneBy(['name' => 'Deputy']);

        $this->assertSame("Deputy", $role->getName());
    }
}

