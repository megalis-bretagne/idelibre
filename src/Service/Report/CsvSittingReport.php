<?php

namespace App\Service\Report;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Timestamp;
use App\Repository\ConvocationRepository;
use App\Service\Util\DateUtil;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;

class CsvSittingReport
{
    public function __construct(
        private readonly ConvocationRepository $convocationRepository,
        private readonly DateUtil $dateUtil
    ) {
    }


    /**
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function generate(Sitting $sitting): string
    {
        $csvPath = '/tmp/' . uniqid('csv_report');
        $writer = Writer::createFromPath($csvPath, 'w+');
        $writer->insertOne($this->getHeaders());

        $convocations = $this->convocationRepository->getEveryoneInSitting($sitting);
        foreach ($convocations as $convocation) {
            $writer->insertOne($this->getConvocationData($convocation));
        }

        return $csvPath;
    }

    private function getHeaders(): array
    {
        return ['Prénom', 'Nom', 'Envoi', 'Réception', 'Role'];
    }

    private function getConvocationData(Convocation $convocation): array
    {
        $structure = $convocation->getSitting()->getStructure();

        return [
            $convocation->getUser()->getFirstName(),
            $convocation->getUser()->getLastName(),
            $this->getDateFormattedTimeStamp($convocation->getSentTimestamp(), $structure),
            $this->getDateFormattedTimeStamp($convocation->getReceivedTimestamp(), $structure),
            $convocation->getUser()->getRole()->getPrettyName(),
        ];
    }

    private function getDateFormattedTimeStamp(?Timestamp $timestamp, Structure $structure): string
    {
        if (!$timestamp) {
            return '';
        }

        return $this->dateUtil->getFormattedDateTime($timestamp->getCreatedAt(), $structure->getTimezone()->getName());
    }

    private function formatConvocationAttendance($convocation): string
    {
        return match ($convocation) {
            'remote' => 'Présent à distance',
            'absent' => 'Absent',
            'present' => 'Présent',
            'poa' => 'Donne pouvoir par procuration',
            'deputy' => 'Remplacé par son suppléant',
            default => '',
        };
    }

    private function setMandatorDeputy($convocation): string
    {
        if ($convocation->getDeputy()) {
            return $convocation->getDeputy()->getFirstName() . ' ' . $convocation->getDeputy()->getLastName();
        }

        if ($convocation->getMandator()) {
            return $convocation->getMandator()->getFirstName() . ' ' . $convocation->getMandator()->getLastName();
        }

        return '';
    }
}
