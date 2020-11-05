<?php


namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Zip\ZipSittingGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenZipSittingHandler implements MessageHandlerInterface
{
    /**
     * @var ZipSittingGenerator
     */
    private ZipSittingGenerator $zipSittingGenerator;
    /**
     * @var SittingRepository
     */
    private SittingRepository $sittingRepository;

    public function __construct(ZipSittingGenerator $zipSittingGenerator, SittingRepository $sittingRepository)
    {
        $this->zipSittingGenerator = $zipSittingGenerator;
        $this->sittingRepository = $sittingRepository;
    }

    public function __invoke(UpdatedSitting $genZipSitting)
    {
        $sitting = $this->sittingRepository->findOneBy(['id' => $genZipSitting->getSittingId()]);
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        $this->zipSittingGenerator->generateZipSitting($sitting);
    }
}
