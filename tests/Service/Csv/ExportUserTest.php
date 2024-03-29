<?php

namespace App\Tests\Service\Csv;

use App\Service\Csv\ExportUsersCsv;
use App\Service\Util\Sanitizer;
use App\Tests\Story\GroupStory;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ExportUserTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private ExportUsersCsv $exportUsersCsv;
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->exportUsersCsv = self::getContainer()->get(ExportUsersCsv::class);
        $this->sanitizer = self::getContainer()->get(Sanitizer::class);
    }

    public function testExportCsvUserFromStructure()
    {
        $structure = StructureStory::libriciel()->object();
        $csvPath = $this->exportUsersCsv->exportStructureUsers($structure);
        $this->assertSame('/tmp/' . $this->sanitizer->fileNameSanitizer($structure->getName(), 255) . '.csv', $csvPath);
    }
}
