<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\File\Generator\FileGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenZipSittingHandler
{
    public function __construct(
        private readonly FileGenerator          $fileGenerator,
        private readonly SittingRepository      $sittingRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger
    )
    {
    }

    public function __invoke(UpdatedSitting $genFullSitting): void
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAndAnnexes($genFullSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genFullSitting->getSittingId() . 'does not exists');
        }

        try {
            $this->fileGenerator->genFullSittingZip($sitting);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
