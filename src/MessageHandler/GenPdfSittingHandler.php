<?php


namespace App\MessageHandler;


use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\Zip\ZipSittingGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenPdfSittingHandler implements MessageHandlerInterface
{

    private PdfSittingGenerator $pdfSittingGenerator;
    private SittingRepository $sittingRepository;

    public function __construct(PdfSittingGenerator $pdfSittingGenerator, SittingRepository $sittingRepository)
    {
        $this->pdfSittingGenerator = $pdfSittingGenerator;
        $this->sittingRepository = $sittingRepository;
    }

    public function __invoke(UpdatedSitting $genZipSitting)
    {
        $sitting = $this->sittingRepository->findOneBy(['id' => $genZipSitting->getSittingId()]);
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        $this->pdfSittingGenerator->generateFullSittingPdf($sitting);
    }
}
