<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('noSuffix', [$this, 'removeSuffix']),
            new TwigFilter('minutesOrHours', [$this, 'formatMinutesOrHours']),
        ];
    }

    public function removeSuffix(string $username): string
    {
        return preg_replace('/@.*/', '', $username);
    }

    public function formatMinutesOrHours(string $timeInMinutes): string
    {
        if($timeInMinutes % 60 != 0) {
            return $timeInMinutes . ' minutes';
        }

        $timeInHours = $timeInMinutes / 60;

        return $timeInHours > 1 ? $timeInHours . ' heures' : $timeInHours . ' heure';
    }
}
