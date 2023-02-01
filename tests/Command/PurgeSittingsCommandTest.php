<?php

namespace App\Tests\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use App\Tests\Story\StructureStory;
use DateTime;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function Symfony\Component\String\s;

class PurgeSittingsCommandTest extends WebTestCase
{
    private SittingRepository $sittingRepository;
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->structureRepository = self::getContainer()->get(StructureRepository::class);
        $this->sittingRepository = self::getContainer()->get(SittingRepository::class);
        $this->sittingManager = self::getContainer()->get(SittingManager::class);

        self::ensureKernelShutdown();
        $this->client = self::createClient();
    }

    public function testPurgeSittings()
    {
        $structure = StructureStory::libriciel();
        $numberSittings = count($this->sittingRepository->findAll());
        $date =date("d/m/y");
        $expected ="Confirmez-vous vouloir purger les seances d'avant le {$date} de la structure {$structure->getName()} ? \n" .
            "({$numberSittings} Séances)(y/n)\n"
        . " Operation annulée\n";

        $cmdToTest = (new Application(self::$kernel))->find('purge:sitting');
        $cmdTester = new CommandTester($cmdToTest);

        $cmdTester->setInputs(['n']);
        $cmdTester->execute([
            'structureId' => $structure->getId(),
            'before' => $date
        ]);


        $cmdTester->assertCommandIsSuccessful();
        $displayedMsg = $cmdTester->getDisplay();
        $this->assertEquals($expected, $displayedMsg );



//        $display = $cmdTester->getDisplay();
//        $this->assertEquals('[OK] Séances supprimées', str_replace("\n", '', trim($display)));
    }


}