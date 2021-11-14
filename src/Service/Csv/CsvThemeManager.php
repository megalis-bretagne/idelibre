<?php

namespace App\Service\Csv;

use App\Entity\Structure;
use App\Service\Theme\ThemeManager;
use ForceUTF8\Encoding;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class CsvThemeManager
{
    public function __construct(
        private ThemeManager $themeManager
    )
    {
    }

    /**
     * @return ConstraintViolationListInterface[]
     */
    public function importThemes(UploadedFile $file, Structure $structure): array
    {
        $errors = [];

        /** @var Reader $csv */
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        foreach ($records as $record) {
            if ($this->isMissingFields($record)) {
                $errors[] = $this->missingFieldViolation($record);
                continue;
            }

            $themeName = $this->sanitize($record[0] ?? '');
            $this->themeManager->createThemesFromString($themeName, $structure);
        }

        return $errors;
    }

    private function isMissingFields(array $record): bool
    {
        return 1 > count($record);
    }

    private function missingFieldViolation($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Chaque ligne doit contenir 1 champs séparés par des virgules.',
            null,
            $record,
            null,
            'le nombre de champs',
            'le nombre de champs est faux'
        );

        return new ConstraintViolationList([$violation]);
    }


    private function sanitize(string $content): string
    {
        $trim_content = trim($content);
        return Encoding::toUTF8($trim_content);
    }
}
