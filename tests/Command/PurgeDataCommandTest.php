<?php

namespace App\Tests\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PurgeDataCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->structureRepository = self::getContainer()->get(StructureRepository::class);
        $this->sittingRepository = self::getContainer()->get(SittingRepository::class);
        $this->sittingManager = self::getContainer()->get(SittingManager::class);

        self::ensureKernelShutdown();
        $this->client = self::createClient();
    }

    public function testPurgeData()
    {
        $cmdToTest = (new Application(self::$kernel))->find('purge:structures');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
        $display = $cmdTester->getDisplay();
        $this->assertEquals('[OK] Séances supprimées', str_replace("\n", '', trim($display)));
    }
}
