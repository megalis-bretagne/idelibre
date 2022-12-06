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
        $this->sanitizeEncrypted($pdfDocPaths);

        return true;
    }

    /**
     * @param array<string> $pdfDocPaths
     */
    private function sanitizeEncrypted(array $pdfDocPaths): void
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

            $this->removeEncryption($encryptedFiles);
        }
    }

    /**
     * @param array<string> $encryptedFiles *
     */
    private function removeEncryption(array $encryptedFiles): void
    {
        foreach ($encryptedFiles as $ef) {
            $cmd = 'qpdf --decrypt' . ' ' . $ef . ' ' . '--replace-input';
            shell_exec($cmd);
        }
    }
}
