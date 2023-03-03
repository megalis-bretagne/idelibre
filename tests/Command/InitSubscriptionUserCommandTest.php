<?php

namespace App\Tests\Command;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class InitSubscriptionUserCommandTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = self::getContainer()->get(UserRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testInitSubscriptionUserCmd()
    {
        $cmdToTest = (new Application(self::$kernel))->find('initBdd:subscription_user');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
    }
}
