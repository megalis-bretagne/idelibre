<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Service\Report\CsvSittingReport;
use App\Service\Report\PdfSittingReport;
use App\Service\Zip\ZipTokenGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ReportSittingController extends AbstractController
{
    /**
     * @Route("/reportSitting/pdf/{id}", name="sitting_report_pdf")
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function pdfReport(Sitting $sitting, PdfSittingReport $pdfSittingReport): Response
    {
        $response = new BinaryFileResponse($pdfSittingReport->generate($sitting));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '_rapport.pdf'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * @Route("/reportSitting/csv/{id}", name="sitting_report_csv")
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function csvReport(Sitting $sitting, CsvSittingReport $csvSittingReport): Response
    {
        $response = new BinaryFileResponse($csvSittingReport->generate($sitting));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '_rapport.csv'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * @Route("/reportSitting/token/{id}", name="sitting_report_token")
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getSittingZipTokens(Sitting $sitting, ZipTokenGenerator $zipTokenGenerator): Response
    {
        $response = new BinaryFileResponse($zipTokenGenerator->generateZipToken($sitting));

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sitting->getName() . '_' . $sitting->getDate()->format('d/m/Y_H_i') . '_jetons.zip'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
