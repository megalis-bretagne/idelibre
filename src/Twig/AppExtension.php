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
        ];
    }

    public function removeSuffix(string $username): string
    {
        return preg_replace('/@.*/', '', $username);
    }
}
