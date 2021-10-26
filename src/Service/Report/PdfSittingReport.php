<?php

namespace App\Service\Report;

use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use Knp\Snappy\Pdf;
use Twig\Environment;

class PdfSittingReport
{
    public function __construct(
        private Pdf $pdf,
        private Environment $twig,
        private ConvocationRepository $convocationRepository
    ) {
    }

    public function generate(Sitting $sitting): string
    {
        $html = $this->twig->render('generate/sitting_report_pdf.html.twig', [
            'convocations' => $this->convocationRepository->getActorConvocationsBySitting($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);

        $generatedPdfPath = '/tmp/' . uniqid('pdf_report');
        $this->pdf->generateFromHtml($html, $generatedPdfPath, [
            'orientation' => 'landscape',
            'footer-right' => '[page] / [toPage]',
        ]);

        return $generatedPdfPath;
    }
}
