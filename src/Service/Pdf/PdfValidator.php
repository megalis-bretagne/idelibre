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

    public function isOpenablePdf(): array
    {
        $success = [];
        if( !empty($_FILES)) {
            if (isset($_FILES['sitting'])) {
                if (!empty($_FILES['sitting']['name']['convocationFile'])) {
                    $filename = $_FILES['sitting']['name']['convocationFile'];
                    $fileContent = file_get_contents($_FILES['sitting']['tmp_name']['convocationFile']);
                    $success[$filename] = false;
                    if (stripos($fileContent, '%PDF') === 0 && substr($fileContent, -5, 4) === "%EOF") {
                        $success[$filename] = true;
                    }
                    if (stristr($fileContent, "/Encrypt")) {
                        $success[$filename] = false;
                    }
                }
                if (!empty($_FILES['sitting']['name']['invitationFile'])) {
                    $filename = $_FILES['sitting']['name']['invitationFile'];
                    $fileContent = file_get_contents($_FILES['sitting']['tmp_name']['invitationFile']);
                    $success[$filename] = false;
                    if (stripos($fileContent, '%PDF') === 0 && substr($fileContent, -5, 4) === "%EOF") {
                        $success[$filename] = true;
                    }
                    if (stristr($fileContent, "/Encrypt")) {
                        $success[$filename] = false;
                    }
                }
            } else {
                foreach ($_FILES as $projectUploaded) {
                    if( $projectUploaded['type'] === 'application/pdf' ) {
                        $filename = $projectUploaded['name'];
                        $fileContent = file_get_contents($projectUploaded['tmp_name']);
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
        }
        return $success;
    }
}
