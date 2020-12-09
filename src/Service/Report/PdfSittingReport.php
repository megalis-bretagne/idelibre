<?php

namespace App\Service\Report;

use App\Entity\Sitting;
use Knp\Snappy\Pdf;
use Twig\Environment;

class PdfSittingReport
{
    private Pdf $pdf;
    private Environment $twig;

    public function __construct(Pdf $pdf, Environment $twig)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
    }

    public function generate(Sitting $sitting): string
    {
        $html = $this->twig->render('generate/sitting_report_pdf.html.twig', [
            'convocations' => $sitting->getConvocations(),
            'sitting' => $sitting,
        ]);

        $generatedPdfPath = '/tmp/' . uniqid('pdf_report');
        $this->pdf->generateFromHtml($html, $generatedPdfPath, ['orientation' => 'landscape']);

        return $generatedPdfPath;
    }
}
