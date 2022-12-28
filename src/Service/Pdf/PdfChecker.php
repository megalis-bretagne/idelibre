<?php

namespace App\Service\Pdf;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfChecker
{
    public function __construct(
        private LoggerInterface $logger,
        private ParameterBagInterface $bag
    ) {
    }

    /**
     * @param array<string> $pdfDocPaths
     */
    public function isValid(array $pdfDocPaths): bool
    {
        $this->sanitizeEncrypted($pdfDocPaths);

        return $this->sizeChecker($pdfDocPaths);
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

    /**
     * @param array<string> $pdfDocPaths
     */
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
