<?php


namespace App\Service\Pdf;


use App\Service\ApiEntity\ProjectApi;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfValidator
{
    public function isPdfFile(?string $fileName): bool
    {
        if(!$fileName) {
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
     * @return bool
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

}
