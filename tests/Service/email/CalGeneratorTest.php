<?php

namespace App\Tests\Service\email;

use App\Service\Email\CalGenerator;
use App\Tests\Factory\ReminderFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CalGeneratorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private CalGenerator $generator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->generator = self::getContainer()->get(CalGenerator::class);
        self::ensureKernelShutdown();
    }

    public function testGenerate()
    {
        $structure = StructureStory::libriciel()->object();
        $sitting = SittingFactory::createOne([
            'structure' => $structure,
            'date' => new DateTime(),
            'reminder' => ReminderFactory::createOne(['isActive' => true]),
        ])->object();
        $calGenerated = $this->generator->generate($sitting);

        $this->assertStringContainsString('/tmp/cal/', $calGenerated);
    }

    public function testGetEndDatetimeWithTz()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $sittingDateTime = $sitting->getDate();
        $durationInMinutes = '360';
        $timezoneName = 'Indian/Reunion';

        $endDatetimeWithTz = $this->generator->getEndDatetimeWithTz($sittingDateTime, $durationInMinutes, $timezoneName);
        $this->assertSame(gettype(new DateTime()), gettype($endDatetimeWithTz));
    }

    public function testGetStartDatetimeWithTz()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $sittingDateTime = $sitting->getDate();
        $durationInMinutes = '360';
        $timezoneName = 'Indian/Reunion';

        $endDatetimeWithTz = $this->generator->getEndDatetimeWithTz($sittingDateTime, $durationInMinutes, $timezoneName);
        $this->assertSame(gettype(new DateTime()), gettype($endDatetimeWithTz));
    }
}
