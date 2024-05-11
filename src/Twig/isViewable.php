<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class isViewable extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('isViewable', [$this, 'isViewable']),
        ];
    }

    public function isViewable(string $filename): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return strtolower($extension) === 'pdf';
    }
}
