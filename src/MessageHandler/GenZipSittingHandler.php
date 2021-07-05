<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Zip\ZipSittingGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenZipSittingHandler implements MessageHandlerInterface
{
    private ZipSittingGenerator $zipSittingGenerator;
    private SittingRepository $sittingRepository;
    private EntityManagerInterface $em;

    public function __construct(ZipSittingGenerator $zipSittingGenerator, SittingRepository $sittingRepository, EntityManagerInterface $em)
    {
        $this->zipSittingGenerator = $zipSittingGenerator;
        $this->sittingRepository = $sittingRepository;
        $this->em = $em;
    }

    public function __invoke(UpdatedSitting $genZipSitting)
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAndAnnexes($genZipSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        $this->zipSittingGenerator->generateZipSitting($sitting);
    }
}
