<?php

namespace App\Service\Util;

use DateTimeInterface;
use DateTimeZone;

class DateUtil
{
    public function getFormattedTime(DateTimeInterface $dateTime, ?string $timezone = null): string
    {
        if ($timezone) {
            $dateTime = $dateTime->setTimezone(new DateTimeZone($timezone));
        }

        return $dateTime->format('H:i');
    }

    public function getFormattedDate(DateTimeInterface $dateTime, ?string $timezone = null): string
    {
        if ($timezone) {
            $dateTime = $dateTime->setTimezone(new DateTimeZone($timezone));
        }

        return $dateTime->format('d/m/Y');
    }

    public function getFormattedDateTime(DateTimeInterface $dateTime, ?string $timezone = null): string
    {
        return $this->getFormattedDate($dateTime) . ' - ' . $this->getFormattedTime($dateTime, $timezone);
    }

    public function getUnderscoredDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('d_m_Y');
    }
}
