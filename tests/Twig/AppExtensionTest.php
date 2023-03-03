<?php

namespace App\Tests\Twig;

use App\Twig\AppExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppExtensionTest extends WebTestCase
{
    public function testFormatMinutesOrHours()
    {
        $appExtension = new AppExtension();
        $this->assertSame("10 heures", $appExtension->formatMinutesOrHours(600));
        $this->assertSame("30 minutes", $appExtension->formatMinutesOrHours(30));
        $this->assertSame("90 minutes", $appExtension->formatMinutesOrHours(90));
    }
}
