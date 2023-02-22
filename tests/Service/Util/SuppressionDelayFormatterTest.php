<?php

namespace App\Tests\Service\Util;

use App\Service\Util\SuppressionDelayFormatter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SuppressionDelayFormatterTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private SuppressionDelayFormatter $suppressionDelayFormatter;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->suppressionDelayFormatter = self::getContainer()->get(SuppressionDelayFormatter::class);
        self::ensureKernelShutdown();
    }

    public function testFormatDelay()
    {
        $value = '6 months';
        $expected = '6 mois';

        $formatted = $this->suppressionDelayFormatter->formatDelay($value);
        $this->assertEquals($expected, $formatted);
    }
}
