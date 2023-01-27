<?php

namespace App\Service\Pdf;

use App\Service\ApiEntity\OtherdocApi;
use App\Service\ApiEntity\ProjectApi;
use Howtomakeaturn\PDFInfo\PDFInfo;

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
        if( isset($projects['sitting']) && !empty($projects['sitting']) ) {
            foreach( $projects['sitting'] as $typeDocument => $dataDocument ) {
                if (!empty($projects['sitting'][$typeDocument])) {
                    $filename = $projects['sitting'][$typeDocument]->getClientOriginalName();
                    $fileContent = file_get_contents($projects['sitting'][$typeDocument]->getPathname());
                    $success[$filename] = false;
                    if (stripos($fileContent, '%PDF') === 0 && substr($fileContent, -5, 4) === "%EOF") {
                        $success[$filename] = true;
                    }

                    if (stristr($fileContent, "/Encrypt")) {
                        $success[$filename] = false;
                    }
                }
            }
        }
        return $success;
    }
    public function listOfOpenablePdfWhenEditingProjects(?array $projects): array
    {
        $success = [];
        foreach ($projects as $tmpName => $projectUploaded) {
            if( $projectUploaded->getMimeType() === 'application/pdf' ) {
                $filename = $projectUploaded->getClientOriginalName();
                $fileContent = file_get_contents($projectUploaded->getPathname());
                $success[$filename] = false;
                if (stripos($fileContent, '%PDF') === 0 && substr($fileContent, -5, 4) === "%EOF") {
                    $success[$filename] = true;
                }
                if (stristr($fileContent, "/Encrypt")) {
                    $success[$filename] = false;
                }
            }
        }
        return $success;
    }



    public function listOfOpenablePdfWhenEditingOtherdocs(?array $otherdocs): array
    {
        $success = [];
        foreach ($otherdocs as $tmpName => $otherdocUploaded) {
            if( $otherdocUploaded->getMimeType() === 'application/pdf' ) {
                $filename = $otherdocUploaded->getClientOriginalName();
                $fileContent = file_get_contents($otherdocUploaded->getPathname());
                $success[$filename] = false;
                if (stripos($fileContent, '%PDF') === 0 && substr($fileContent, -5, 4) === "%EOF") {
                    $success[$filename] = true;
                }
                if (stristr($fileContent, "/Encrypt")) {
                    $success[$filename] = false;
                }
            }
        }
        return $success;
    }

}
