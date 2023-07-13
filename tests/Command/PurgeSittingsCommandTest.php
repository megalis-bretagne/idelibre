<?php

namespace App\Tests\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use App\Tests\Factory\StructureFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PurgeSittingsCommandTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->structureRepository = self::getContainer()->get(StructureRepository::class);
        $this->sittingRepository = self::getContainer()->get(SittingRepository::class);
        $this->sittingManager = self::getContainer()->get(SittingManager::class);

        self::ensureKernelShutdown();
        $this->client = self::createClient();
    }

    public function testPurgeSittingsConfirmNo()
    {
        $structure = StructureFactory::createOne();
        $numberSittings = count($this->sittingRepository->findAll());
        $date = date('d/m/yy');
        $expected = "Confirmez-vous vouloir purger les seances d'avant le {$date} de la structure {$structure->getName()} ? \n(0 Séances)(y/n) Operation annulée\n";

        $cmdToTest = (new Application(self::$kernel))->find('purge:sitting');
        $cmdTester = new CommandTester($cmdToTest);

        $cmdTester->setInputs(['n']);
        $cmdTester->execute([
            'structureId' => $structure->getId(),
            'before' => $date,
        ]);

        $cmdTester->assertCommandIsSuccessful();
        $displayedMsg = $cmdTester->getDisplay();
        $this->assertEquals($expected, $displayedMsg);
    }

    public function testPurgeSittingsConfirmYes()
    {
        $structure = StructureFactory::createOne();
        $numberSittings = count($this->sittingRepository->findAll());
        $date = date('d/m/yy');
        $expected = "Confirmez-vous vouloir purger les seances d'avant le {$date} de la structure {$structure->getName()} ? " .
            "({$numberSittings} Séances)(y/n) [OK] Séances supprimées";

        $cmdToTest = (new Application(self::$kernel))->find('purge:sitting');
        $cmdTester = new CommandTester($cmdToTest);

        $cmdTester->setInputs(['y']);
        $cmdTester->execute([
            'structureId' => $structure->getId(),
            'before' => $date,
        ]);

        $cmdTester->assertCommandIsSuccessful();
        $display = $cmdTester->getDisplay();
        $this->assertEquals($expected, str_replace("\n", '', trim($display)));
    }
}
