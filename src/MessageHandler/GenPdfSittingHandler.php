<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenPdfSittingHandler
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
    public function __invoke(UpdatedSitting $genFullSitting): void
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAnnexesAndOtherDocs($genFullSitting->getSittingId());

        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genFullSitting->getSittingId() . 'does not exists');
        }

        $this->fileGenerator->genFullSittingPdf($sitting);
    }
}
