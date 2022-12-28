<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SizeFormatter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('sizeFormatter', [$this, 'sizeFormatter']),
        ];
    }
    public function sizeFormatter($rawSize): string
    {
        $kilooctets = 1024;
        $megaoctets = $kilooctets * 1024;
        $gigaoctets = $megaoctets * 1024;

        if ($rawSize < $megaoctets) {
            return number_format($rawSize / $kilooctets, 2, '.') . ' Ko';
        } elseif ($rawSize < $gigaoctets) {
            return number_format($rawSize / $megaoctets, 2, '.') . ' Mo';
        }
        return $rawSize . ' O';
    }
}