<?php

namespace App\Service\File\Generator;

use App\Entity\Sitting;
use Howtomakeaturn\PDFInfo\PDFInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileChecker
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly LoggerInterface $logger
    ) {
    }

    public function isValid(string $extension, ?array $fullSittingDocsPath = null, ?Sitting $sitting = null): bool
    {
        if ('pdf' == $extension) {
            $this->sanitizeEncrypted($fullSittingDocsPath);

            return $this->pdfSizeChecker($fullSittingDocsPath);
        }

        return $this->sizeChecker($sitting);
    }

    /**
     * @param array<string> $fullSittingDocsPath
     */
    public function sanitizeEncrypted(array $fullSittingDocsPath): void
    {
        $encryptedFiles = [];
        foreach ($fullSittingDocsPath as $path) {
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
     * @param array<string> $encryptedFiles
     */
    private function removeEncryption(array $encryptedFiles): void
    {
        foreach ($encryptedFiles as $ef) {
            $cmd = 'qpdf --decrypt' . ' ' . $ef . ' ' . '--replace-input';
            shell_exec($cmd);
        }
    }

    /**
     * @param array<string> $fullSittingDocsPath
     */
    private function pdfSizeChecker(array $fullSittingDocsPath): bool
    {
        $fileSizeArray = [];

        foreach ($fullSittingDocsPath as $path) {
            $pdfInfo = new PDFInfo($path);
            $fileSizeArray[] = $pdfInfo->fileSize;
        }
        $totalFileSize = array_sum($fileSizeArray);

        if ($this->bag->get('maximum_size_pdf_zip_generation') <= $totalFileSize) {
            return false;
        }

        return true;
    }

    public function sizeChecker(Sitting $sitting): bool
    {
        $projects = $sitting->getProjects();
        $fileSizeArray = [];

        foreach ($projects as $project) {
            $fileSizeArray[] = $project->getFile()->getSize();
            foreach ($project->getAnnexes() as $annex) {
                $fileSizeArray[] = $annex->getFile()->getSize();
            }
        }
        $totalFileSize = array_sum($fileSizeArray);

        if ($this->bag->get('maximum_size_pdf_zip_generation') <= $totalFileSize) {
            return false;
        }

        return true;
    }
}
