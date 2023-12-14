<?php

namespace App\Service\Util;

use Transliterator;

class Sanitizer
{
    public function fileNameSanitizer(string $name, int $length): string
    {
        $removeAccent = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ->transliterate($name);
        $removeAccent = preg_replace(['/\s+/', '/\'/', "/\//", '/_/'], '-', $removeAccent);
        $isTrimmed = trim($removeAccent);

        return (strlen($isTrimmed) > $length) ? substr($isTrimmed, 0, $length) . '[...]' : $isTrimmed;
    }
}
