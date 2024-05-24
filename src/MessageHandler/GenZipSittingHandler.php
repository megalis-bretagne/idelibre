<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\SittingZipGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenZipSittingHandler
{
    public function __construct(
        private readonly SittingZipGenerator $sittingZipGenerator,
        private readonly SittingRepository $sittingRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(UpdatedSitting $genFullSitting): void
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAnnexesAndOtherDocs($genFullSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genFullSitting->getSittingId() . 'does not exists');
        }

        try {
            $this->sittingZipGenerator->genZip($sitting);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
