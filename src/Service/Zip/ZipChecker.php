<?php

namespace App\Service\Zip;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ZipChecker
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
    ) {
    }

    public function isValid(array $pdfDocPaths): bool
    {
        return $this->sizeChecker($pdfDocPaths);
    }

    private function sizeChecker(array $pdfDocPaths): bool
    {
        $fileSizeArray = [];

        foreach ($pdfDocPaths as $path) {
            $pdfInfo = new PDFInfo($path);
            $fileSizeArray[] = $pdfInfo->fileSize;
        }
        $totalFileSize = array_sum($fileSizeArray);

        if ($this->bag->get('maximum_size_pdf_zip_generation') <= $totalFileSize) {
            return false;
        }

        return true;
    }
}
