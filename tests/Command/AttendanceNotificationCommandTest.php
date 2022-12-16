<?php

namespace App\Tests\Command;

use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AttendanceNotificationCommandTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private UserRepository $userRepository;
    private StructureRepository $structureRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->structureRepository = self::getContainer()->get(StructureRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        StructureStory::libriciel();
        UserStory::load();
        SittingStory::load();
        ConvocationStory::load();
    }
    public function testGetAttendanceNotification()
    {
        $consulePwd = (new Application(self::$kernel))->find('attendance:notification');

        $commandTester = new CommandTester($consulePwd);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $display = $commandTester->getDisplay();
        $this->assertEquals("[OK] OK", str_replace("\n", "", trim($display)));

    }
}