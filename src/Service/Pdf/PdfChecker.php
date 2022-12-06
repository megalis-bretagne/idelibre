<?php

namespace App\Service\Pdf;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Psr\Log\LoggerInterface;

class PdfChecker
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @param array<string> $pdfDocPaths
     */
    public function isValid(array $pdfDocPaths): bool
    {
        $encryptedFiles = $this->checkEncrypted($pdfDocPaths);

        return empty($encryptedFiles);
    }

    /**
     * @param array<string> $pdfDocPaths
     *
     * @return array<string>
     */
    private function checkEncrypted(array $pdfDocPaths): array
    {
        $encryptedFiles = [];
        foreach ($pdfDocPaths as $path) {
            $pdfInfo = new PDFInfo($path);
            if ('no' !== $pdfInfo->encrypted) {
                $encryptedFiles[] = $path;
            }
        }

        if (!empty($encryptedFiles)) {
            $this->logger->error("les fichiers suivants sont 'encrypted' : " . implode(',', $encryptedFiles));
        }

        return $encryptedFiles;
    }
}
