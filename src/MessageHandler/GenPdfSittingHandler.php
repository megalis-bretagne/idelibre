<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenPdfSittingHandler implements MessageHandlerInterface
{
    private FileGenerator $fileGenerator;
    private SittingRepository $sittingRepository;
    private EntityManagerInterface $em;

    public function __construct(FileGenerator $fileGenerator, SittingRepository $sittingRepository, EntityManagerInterface $em)
    {
        $this->fileGenerator = $fileGenerator;
        $this->sittingRepository = $sittingRepository;
        $this->em = $em;
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function __invoke(UpdatedSitting $genFullSitting)
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAndAnnexes($genFullSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genFullSitting->getSittingId() . 'does not exists');
        }
        dd();
        $this->fileGenerator->genFullSittingPdf($sitting);
    }
}
