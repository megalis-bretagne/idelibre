<?php

namespace App\Tests\Service\Util;

use App\Service\Util\Converter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConverterTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private Converter $converter;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->converter = self::getContainer()->get(Converter::class);
        self::ensureKernelShutdown();
    }

    public function testBytesConverter()
    {
        $value = '32Go';

        $converted_value = $this->converter->bytesConverter($value);

        $this->assertIsFloat($converted_value);
    }

    public function testBytesConverterWithWrongValue()
    {
        $value = 'WrongValue';

        $converted_value = $this->converter->bytesConverter($value);

        $this->assertEquals(0.0, $converted_value);
    }
}
