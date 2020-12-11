<?php

namespace App\Tests\Service\Util;

use App\Service\Util\GenderConverter;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GenderConverterTest extends WebTestCase
{
    public function testFormatMALE()
    {
        $converter = new GenderConverter();
        $this->assertEquals('Monsieur', $converter->format(GenderConverter::MALE));
    }

    public function testFormatFEMALE()
    {
        $converter = new GenderConverter();
        $this->assertEquals('Madame', $converter->format(GenderConverter::FEMALE));
    }

    public function testFormatNotDefined()
    {
        $converter = new GenderConverter();
        $this->assertEquals('', $converter->format(GenderConverter::NOT_DEFINED));
    }

    public function testFormatNull()
    {
        $converter = new GenderConverter();
        $this->assertEquals('', $converter->format(null));
    }

    public function testFormatNotExists()
    {
        $converter = new GenderConverter();
        $this->assertEquals('', $converter->format(99));
    }

}
