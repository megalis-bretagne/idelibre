<?php

namespace App\Service\Report;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use League\Csv\Writer;

class CsvSittingReport
{
    public function generate(Sitting $sitting): string
    {
        $csvPath = '/tmp/' . uniqid('csv_report');
        $writer = Writer::createFromPath($csvPath, 'w+');

        foreach ($sitting->getConvocations() as $convocation) {
            $writer->insertOne($this->getConvocationData($convocation));
        }

        return $csvPath;
    }

    private function getConvocationData(Convocation $convocation): array
    {
        return [
            $convocation->getUser()->getFirstName(),
            $convocation->getUser()->getLastName(),
            $this->getDateFormattedTimeStamp($convocation->getSentTimestamp()),
            $this->getDateFormattedTimeStamp($convocation->getReceivedTimestamp()),
        ];
    }

    private function getDateFormattedTimeStamp(?Timestamp $timestamp): string
    {
        if (!$timestamp) {
            return '';
        }

        return $timestamp->getCreatedAt()->format('d/m/Y');
    }
}
