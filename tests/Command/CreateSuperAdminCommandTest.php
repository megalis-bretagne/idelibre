<?php

namespace App\Tests\Command;

use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\RoleStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateSuperAdminCommandTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
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

        RoleStory::load();
    }

    public function testCreateSuperAdminCommand()
    {
        $expected = sprintf('L\'utilisateur "superadmin" a bien été enregistré avec le mot de passe :');

        $cmdToTest = (new Application(self::$kernel))->find('admin:create:superadmin');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);

        $cmdTester->assertCommandIsSuccessful();
        $displayedMsg = $cmdTester->getDisplay();

        $this->assertStringContainsString($expected, $displayedMsg);
        $user = $this->userRepository->findOneBy(['username' => 'superadmin']);
        $this->assertSame('superadmin', $user->getUsername());
    }
}
