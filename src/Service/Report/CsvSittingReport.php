<?php

namespace App\Service\Report;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Repository\ConvocationRepository;
use League\Csv\Writer;

class CsvSittingReport
{

    private ConvocationRepository $convocationRepository;

    public function __construct(ConvocationRepository $convocationRepository)
    {
        $this->convocationRepository = $convocationRepository;
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
        return [
            $convocation->getUser()->getFirstName(),
            $convocation->getUser()->getLastName(),
            $this->getDateFormattedTimeStamp($convocation->getSentTimestamp()),
            $this->getDateFormattedTimeStamp($convocation->getReceivedTimestamp()),
            $convocation->getAttendance() ?? '',
            $convocation->getDeputy() ?? '',
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
