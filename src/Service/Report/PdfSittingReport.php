<?php

namespace App\Service\Report;

use App\Entity\Sitting;
use App\Repository\ConvocationRepository;
use Knp\Snappy\Pdf;
use Twig\Environment;

class PdfSittingReport
{
    private Pdf $pdf;
    private Environment $twig;
    private ConvocationRepository $convocationRepository;

    public function __construct(Pdf $pdf, Environment $twig, ConvocationRepository $convocationRepository)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
        $this->convocationRepository = $convocationRepository;
    }

    public function generate(Sitting $sitting): string
    {
        //<td class="col-3">{{ sitting.date | date('d/m/Y : H:i' , timezone) }} </td>
        $html = $this->twig->render('generate/sitting_report_pdf.html.twig', [
            'convocations' => $this->convocationRepository->getActorConvocationsBySitting($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);

        $generatedPdfPath = '/tmp/' . uniqid('pdf_report');
        $this->pdf->generateFromHtml($html, $generatedPdfPath, ['orientation' => 'landscape']);

        return $generatedPdfPath;
    }
}
