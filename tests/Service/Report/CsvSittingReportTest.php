<?php

namespace App\Tests\Service\Report;

use App\Service\Report\CsvSittingReport;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CsvSittingReportTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private CsvSittingReport $csvSittingReport;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->csvSittingReport = self::getContainer()->get(CsvSittingReport::class);

        self::ensureKernelShutdown();
    }

    public function testGeneration()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();

        $csvPath = $this->csvSittingReport->generate($sitting);

        $this->assertStringContainsString('/tmp/csv_report', $csvPath);
    }
}
