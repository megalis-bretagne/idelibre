<?php

namespace App\Service\Pdf;

use App\Service\ApiEntity\OtherdocApi;
use App\Service\ApiEntity\ProjectApi;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfValidator
{
    public function isPdfFile(?string $fileName): bool
    {
        if (!$fileName) {
            return false;
        }

        if (!strpos($fileName, '.')) {
            return false;
        }

        $exploded = (explode('.', $fileName));
        $extension = $exploded[count($exploded) - 1];

        return 'pdf' === $extension || 'PDF' === $extension;
    }

    /**
     * @param ProjectApi[] $projects
     */
    public function isProjectsPdf(array $projects): bool
    {
        foreach ($projects as $project) {
            if (!$this->isPdfFile($project->getFileName())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param OtherdocApi[] $otherdocs
     */
    public function isOtherdocsPdf(array $otherdocs): bool
    {
        foreach ($otherdocs as $otherdoc) {
            if (!$this->isPdfFile($otherdoc->getFileName())) {
                return false;
            }
        }

        return true;
    }

    public function listOfOpenablePdfForSittingCreation(?array $projects): array
    {
        $success = [];
        if (isset($projects['sitting']) && !empty($projects['sitting'])) {
            foreach ($projects['sitting'] as $typeDocument => $dataDocument) {
                if (!empty($projects['sitting'][$typeDocument])) {
                    $filename = $projects['sitting'][$typeDocument]->getClientOriginalName();

                    $handle = fopen($projects['sitting'][$typeDocument]->getPathname(), "rb");
                    $contents = fread($handle, filesize($projects['sitting'][$typeDocument]->getPathname()));
                    fclose($handle);

                    $success[$filename] = [
                        $this->isPdfContent($contents),
                        !$this->isProtectedByPasswordPdf($projects['sitting'][$typeDocument]->getPathname()),
                    ];
                }
            }
        }

        return $success;
    }

    /**
     * @param array<UploadedFile $uploadedFiles
     */
    public function listOfOpenablePdfWhenAddingFiles(array $uploadedFiles): array
    {
        $success = [];
        foreach ($uploadedFiles as $uploadedFile) {
            if ($this->isPdfMimeType($uploadedFile)) {
                $filename = $uploadedFile->getClientOriginalName();

                $handle = fopen($uploadedFile->getPathname(), "rb");
                $contents = fread($handle, filesize($uploadedFile->getPathname()));
                fclose($handle);

                $success[$filename] = [
                    $this->isPdfContent($contents),
                    !$this->isProtectedByPasswordPdf($uploadedFile->getPathname()),
                ];
            }
        }

        return $success;
    }

    public function isPdfMimeType(UploadedFile $uploadedFile): bool
    {
        return 'application/pdf' === $uploadedFile->getMimeType();
    }

    public function isPdfContent($contentPdf): bool
    {
        $success = false;
        $contentPdf = preg_replace('/[\r \n]/', '', $contentPdf);
        if (0 === stripos($contentPdf, '%PDF') && (str_ends_with($contentPdf, '%EOF') || '%EOF' === substr($contentPdf, -5, 4))) {
            $success = true;
        }

        return $success;
    }

    public function isProtectedByPasswordPdf($filePath): bool
    {
        $success = false;
        $cmd = 'pdfinfo ' . $filePath;
        $cmdResult = shell_exec($cmd);
        if (empty($cmdResult)) {
            $success = true;
        }

        return $success;
    }
}
