<?php

namespace App\Tests\Command;

use App\Tests\Factory\UserFactory;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ExportCsvCommandTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testExportCsvCommand()
    {
        $structure = StructureStory::libriciel()->object();
        UserFactory::createMany(5, ['structure' => $structure]);

        $cmdToTest = (new Application(self::$kernel))->find('export:user');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute(['structureId' => $structure->getId()]);
        $cmdTester->assertCommandIsSuccessful();
    }
}