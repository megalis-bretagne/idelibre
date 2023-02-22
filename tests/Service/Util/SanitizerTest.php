<?php

namespace App\Tests\Service\Util;

use App\Service\Util\Sanitizer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SanitizerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->sanitizer = self::getContainer()->get(Sanitizer::class);
        self::ensureKernelShutdown();
    }

    public function testFileNameSanitizer()
    {
        $name = 'Lorem ipsum dolor sit amet consectetur adipiscing elit Vestibulum ut ex urna. Phasellus venenatis et justo id porta Proin egestas dapibus felis, nec mattis ligula mattis id Interdum et malesuada fames ac ante ipsum primis ';
        $length = 100;

        $sanitized_name = $this->sanitizer->fileNameSanitizer($name, $length);

        $this->assertEquals($length, strlen($sanitized_name) - 5);
    }
}
