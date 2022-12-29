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
        $kiloOctets = 1024;
        $megaOctets = $kiloOctets * 1024;
        $gigaOctets = $megaOctets * 1024;

        if ($rawSize < $megaOctets) {
            return number_format($rawSize / $kiloOctets, 2, '.') . ' Ko';
        }
        if ($rawSize < $gigaOctets) {
            return number_format($rawSize / $megaOctets, 2, '.') . ' Mo';
        }

        return number_format($rawSize / $gigaOctets, 2, '.') . ' Go';
    }
}
