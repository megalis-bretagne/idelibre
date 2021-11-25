<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Zip\ZipSittingGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenZipSittingHandler implements MessageHandlerInterface
{
    public function __construct(
        private ZipSittingGenerator $zipSittingGenerator,
        private SittingRepository $sittingRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(UpdatedSitting $genZipSitting)
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAndAnnexes($genZipSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        try {
            $this->zipSittingGenerator->generateZipSitting($sitting);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
