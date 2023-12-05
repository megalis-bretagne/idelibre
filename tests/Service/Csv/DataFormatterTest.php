<?php

namespace App\Tests\Service\Csv;

use Monolog\Test\TestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DataFormatterTest extends TestCase
{
    use ResetDatabase;
    use Factories;

    private $dataFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataFormatter = self::getContainer()->get('App\Service\Csv\DataFormatter');
    }

}
