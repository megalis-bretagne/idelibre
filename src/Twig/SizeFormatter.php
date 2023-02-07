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
        $kiloOctets = 1000;
        $megaOctets = $kiloOctets * 1000;
        $gigaOctets = $megaOctets * 1000;

        if ($rawSize < $megaOctets) {
            return number_format($rawSize / $kiloOctets, 0, '.') . ' Ko';
        }
        if ($rawSize < $gigaOctets) {
            return number_format($rawSize / $megaOctets, 0, '.') . ' Mo';
        }

        return number_format($rawSize / $gigaOctets, 0, '.') . ' Go';
    }
}
