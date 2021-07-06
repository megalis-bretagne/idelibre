<?php

namespace App\Service\Report;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Timestamp;
use App\Repository\ConvocationRepository;
use App\Service\Util\DateUtil;
use League\Csv\Writer;

class CsvSittingReport
{
    private ConvocationRepository $convocationRepository;
    private DateUtil $dateUtil;

    public function __construct(ConvocationRepository $convocationRepository, DateUtil $dateUtil)
    {
        $this->convocationRepository = $convocationRepository;
        $this->dateUtil = $dateUtil;
    }

    public function generate(Sitting $sitting): string
    {
        $csvPath = '/tmp/' . uniqid('csv_report');
        $writer = Writer::createFromPath($csvPath, 'w+');
        $writer->insertOne($this->getHeaders());

        $convocations = $this->convocationRepository->getActorConvocationsBySitting($sitting);
        foreach ($convocations as $convocation) {
            $writer->insertOne($this->getConvocationData($convocation));
        }

        return $csvPath;
    }

    private function getHeaders(): array
    {
        return ['Prénom', 'Nom', 'Envoi', 'Réception', 'Présence', 'Mandataire'];
    }

    private function getConvocationData(Convocation $convocation): array
    {
        $structure = $convocation->getSitting()->getStructure();

        return [
            $convocation->getUser()->getFirstName(),
            $convocation->getUser()->getLastName(),
            $this->getDateFormattedTimeStamp($convocation->getSentTimestamp(), $structure),
            $this->getDateFormattedTimeStamp($convocation->getReceivedTimestamp(), $structure),
            $convocation->getAttendance() ?? '',
            $convocation->getDeputy() ?? '',
        ];
    }

    private function getDateFormattedTimeStamp(?Timestamp $timestamp, Structure $structure): string
    {
        if (!$timestamp) {
            return '';
        }

        return $this->dateUtil->getFormattedDateTime($timestamp->getCreatedAt(), $structure->getTimezone()->getName());
    }
}
