<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class genPdfAndZipSittingCommandTest extends WebTestCase
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

    public function testGeneration()
    {
        $cmdToTest = (new Application(self::$kernel))->find('gen:zip_pdf');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([]);
        $cmdTester->assertCommandIsSuccessful();
        $display = $cmdTester->getDisplay();
        $this->assertEquals('[OK] OK', str_replace("\n", '', trim($display)));
    }
}
