<?php

namespace App\Service\Report;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use Knp\Snappy\Pdf;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PdfSittingReport
{
    public function __construct(
        private readonly Pdf          $pdf,
        private readonly Environment  $twig,
        private readonly ConvocationRepository $convocationRepository
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function generate(Sitting $sitting): string
    {
        $html = $this->twig->render('generate/sitting_report_pdf.html.twig', [
            'convocations' => $this->convocationRepository->getEveryoneInSitting($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
            'attendance' => [
                Convocation::PRESENT => 'Présent',
                Convocation::ABSENT => 'Absent',
                Convocation::REMOTE => 'Distanciel',
                Convocation::ABSENT_GIVE_POA => 'Donne pouvoir',
                Convocation::ABSENT_SEND_DEPUTY => 'Envoie son suppléant',
            ],
        ]);

        $generatedPdfPath = '/tmp/' . uniqid('pdf_report');
        $this->pdf->generateFromHtml($html, $generatedPdfPath, [
            'orientation' => 'landscape',
            'footer-right' => '[page] / [toPage]',
        ]);

        return $generatedPdfPath;
    }
}
