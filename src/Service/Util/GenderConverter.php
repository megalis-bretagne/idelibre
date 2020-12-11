<?php

namespace App\Service\Util;

class GenderConverter
{
    public const NOT_DEFINED = 0;
    public const FEMALE = 1;
    public const MALE = 2;

    public function format(?int $code): string
    {
        switch ($code) {
            case self::FEMALE:
                return 'Madame';
            case self::MALE:
                return 'Monsieur';
            default:
                return '';
        }
    }
}
