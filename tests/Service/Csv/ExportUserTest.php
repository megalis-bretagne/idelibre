<?php

namespace App\Tests\Service\Csv;

use App\Service\Csv\ExportUsersCsv;
use App\Tests\Story\GroupStory;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use function _PHPStan_d5c599c96\RingCentral\Psr7\str;

class ExportUserTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private ExportUsersCsv $exportUsersCsv;

    protected function setUp(): void
    {
        $this->exportUsersCsv = self::getContainer()->get(ExportUsersCsv::class);
    }

    public function testExportCsvUserFromStructure()
    {
        $structure = StructureStory::libriciel()->object();
        $csvPath = $this->exportUsersCsv->exportStructureUsers($structure);
        $this->assertSame('/tmp/export/' . $structure->getName() . '.csv', $csvPath);
    }

    public function testExportCsvUserFromGroup()
    {
        $group = GroupStory::recia()->object();
        $zipPath = $this->exportUsersCsv->exportGroupUsers($group);
        $this->assertStringContainsString('/tmp/', $zipPath);
        $this->assertStringContainsString('.zip', $zipPath);
    }
}
