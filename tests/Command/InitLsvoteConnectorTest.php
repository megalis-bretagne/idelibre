<?php

namespace App\Tests\Command;

use App\Repository\LsvoteConnectorRepository;
use App\Repository\StructureRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class InitLsvoteConnectorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private readonly StructureRepository    $structureRepository;
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->structureRepository = self::getContainer()->get(StructureRepository::class);
        $this->connectorRepository = self::getContainer()->get(LsvoteConnectorRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testInitLsvoteConnector()
    {
        $cmdToTest = (new Application(self::$kernel))->find('initBdd:connector_lsvote');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);

        $cmdTester->assertCommandIsSuccessful();
        $this->connectorRepository->findOneBy(['name' => 'lsvote']);
    }
}
