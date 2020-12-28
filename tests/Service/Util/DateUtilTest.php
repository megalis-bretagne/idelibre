<?php

namespace App\Tests\Service\Util;

use App\Service\Util\DateUtil;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DateUtilTest extends WebTestCase
{
    public function testGetFormattedTimeWithoutTimeZone()
    {
        $dateUtil = new DateUtil();
        $time = new DateTimeImmutable('2020-10-22', new DateTimeZone('Europe/Paris'));
        $this->assertSame('00:00', $dateUtil->getFormattedTime($time));
    }

    public function testGetFormattedTimeWithTimeZone()
    {
        $dateUtil = new DateUtil();
        $time = new DateTimeImmutable('2020-10-22', new DateTimeZone('Europe/Paris'));
        $this->assertSame('02:00', $dateUtil->getFormattedTime($time, 'Indian/Reunion'));
    }

    public function testGetFormattedDateWithoutTimeZone()
    {
        $dateUtil = new DateUtil();
        $time = new DateTimeImmutable('2020-10-22', new DateTimeZone('Europe/Paris'));
        $this->assertSame('22/10/2020', $dateUtil->getFormattedDate($time));
    }

    public function testGetFormattedDateWithTimeZonePreviousDay()
    {
        $dateUtil = new DateUtil();
        $time = new DateTimeImmutable('2020-10-22', new DateTimeZone('Europe/Paris'));
        $this->assertSame('21/10/2020', $dateUtil->getFormattedDate($time, 'America/New_York'));
    }

    public function testGetFormattedDateTime()
    {
        $dateUtil = new DateUtil();
        $time = new DateTimeImmutable('2020-10-22', new DateTimeZone('Europe/Paris'));
        $this->assertSame('22/10/2020 - 00:00', $dateUtil->getFormattedDateTime($time));
    }
}
