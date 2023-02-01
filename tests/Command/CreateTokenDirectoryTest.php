<?php

namespace App\Tests\Command;

use App\Repository\StructureRepository;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTokenDirectoryTest extends WebTestCase
{
    private ?KernelBrowser $client;
    private StructureRepository $structureRepository;

   protected function setUp(): void
   {
        $kernel = self::bootKernel();

       $this->structureRepository = self::getContainer()->get(StructureRepository::class);

       self::ensureKernelShutdown();
        $this->client = self::createClient();

       StructureStory::libriciel();
   }

   public function testCreateTokenDirectory()
   {
       $cmdToTest = (new Application(self::$kernel))->find('createdir:token');
       $cmdTester = new CommandTester($cmdToTest);
       $cmdTester->execute([]);
       $cmdTester->assertCommandIsSuccessful();
       $display = $cmdTester->getDisplay();
       $this->assertEquals('[OK] token dir create', str_replace("\n", '', trim($display)));
   }

}