<?php

namespace App\Service\Util;

class SuppressionDelayFormatter
{
    public const DELAYS = [
        '3 mois' => '3 months',
        '6 mois' => '6 months',
        '1 an' => '1 years',
        '2 ans' => '2 years',
        '3 ans' => '3 years',
        '5 ans' => '5 years',
        '10 ans' => '10 years',
        'Jamais' => '100 years',
    ];

    public function formatDelay(string $value): string
    {
        $flippedDelays = array_flip(self::DELAYS);
        if (empty($flippedDelays[$value])) {
            return $value;
        }

        return $flippedDelays[$value];
    }
}
