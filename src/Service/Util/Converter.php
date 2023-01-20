<?php

namespace App\Service\Util;

class Converter
{
    public function bytesConverter(string $value): float
    {
        $str = trim($value);
        $num = (float) $str;
        if ('B' == strtoupper(substr($str, -1))) {
            $str = substr($str, 0, -1);
        }
        switch (strtoupper(substr($str, -1))) {
            case 'P':  $num *= 1024;
            // no break
            case 'T':  $num *= 1024;
            // no break
            case 'G':  $num *= 1024;
            // no break
            case 'M':  $num *= 1024;
            // no break
            case 'K':  $num *= 1024;
        }

        return $num;
    }
}
