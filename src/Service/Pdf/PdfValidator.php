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
    public function isFilesPdf(array $projects): bool
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

    /**
     * array<?UploadedFile> $uploadedFiles.
     *
     * @return array<string>
     */
    public function getListOfUnreadablePdf(array $uploadedFiles): array
    {
        $pdfStatus = $this->listOfReadablePdfStatus($uploadedFiles);

        return $this->listOfNotReadablePdfName($pdfStatus);
    }

    /**
     * @param array<string, bool[]> $pdfStatus
     *
     * @return array<string>
     */
    private function listOfNotReadablePdfName(array $pdfStatus): array
    {
        $fileNames = [];
        foreach ($pdfStatus as $fileName => $status) {
            if (in_array(false, $status)) {
                $fileNames[] = $fileName;
            }
        }

        return $fileNames;
    }

    /**
     * @param array<?UploadedFile> $uploadedFiles
     *
     * @return array<string, bool[]>
     */
    public function listOfReadablePdfStatus(array $uploadedFiles): array
    {
        $status = [];
        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile) {
                continue;
            }

            if ($this->isPdfMimeType($uploadedFile)) {
                $filename = $uploadedFile->getClientOriginalName();

                $handle = fopen($uploadedFile->getPathname(), 'rb');
                $isGoodPdf = $this->isPdfContent($handle);
                fclose($handle);

                $status[$filename] = [
                    $isGoodPdf,
                    !$this->isProtectedByPasswordPdf($uploadedFile->getPathname()),
                ];
            }
        }

        return $status;
    }

    public function isPdfMimeType(UploadedFile $uploadedFile): bool
    {
        return 'application/pdf' === $uploadedFile->getMimeType();
    }

    public function isPdfContent($handle): bool
    {
        $firstLine = fgets($handle);

        $lastLine = null;
        $beforeLastLine = null;
        while (($line = fgets($handle)) !== false) {
            $beforeLastLine = $lastLine;
            $lastLine = $line;
        }
        return $this->isFirstLinePdf($firstLine) && $this->isLastLineOrBeforeLastLineEOF($lastLine, $beforeLastLine);
    }


    private function isFirstLinePdf(string $firstLine)
    {
        return 0 === stripos($firstLine, '%PDF');
    }

    private function isLastLineOrBeforeLastLineEOF(string $lastLine, string $beforeLastLine)
    {
        $lastLine = preg_replace('/[\r \n]/', '', $lastLine);
        $beforeLastLine = preg_replace('/[\r \n]/', '', $beforeLastLine);
        if (strpos($lastLine, '%%EOF') !== false) {
            return true;
        }
        return (strpos($beforeLastLine, '%%EOF') !== false);
    }

    public function isProtectedByPasswordPdf($filePath): bool
    {
        $cmd = 'pdfinfo ' . $filePath . ' 2>/dev/null';
        $cmdResult = shell_exec($cmd);

        return empty($cmdResult);
    }
}
