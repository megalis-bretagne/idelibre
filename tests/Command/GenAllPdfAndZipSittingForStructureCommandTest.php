<?php

namespace App\Tests\Command;

use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function Symfony\Component\String\s;

class GenAllPdfAndZipSittingForStructureCommandTest extends WebTestCase
{

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
        $structure = StructureStory::libriciel();
        $cmdToTest = (new Application(self::$kernel))->find('gen:all_zip_pdf');
        $cmdTester = new CommandTester($cmdToTest);
        $cmdTester->execute([
            "structureId" => $structure->getId()
        ]);
        $cmdTester->assertCommandIsSuccessful();
        $display = $cmdTester->getDisplay();
        $this->assertEquals('[OK] OK', str_replace("\n", '', trim($display)));
    }


}