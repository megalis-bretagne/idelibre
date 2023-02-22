<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Seance\SittingManager;
use App\Service\Timestamp\TimestampManager;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ModifiedSittingTimestampGeneratorHandler
{
    private SittingRepository $sittingRepository;
    private EntityManagerInterface $em;
    private TimestampManager $timestampManager;
    private LoggerInterface $logger;
    private SittingManager $sittingManager;

    public function __construct(
        TimestampManager $timestampManager,
        SittingRepository $sittingRepository,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        SittingManager $sittingManager
    ) {
        $this->timestampManager = $timestampManager;
        $this->sittingRepository = $sittingRepository;
        $this->em = $em;
        $this->logger = $logger;
        $this->sittingManager = $sittingManager;
    }

    public function __invoke(UpdatedSitting $genZipSitting):void
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAndAnnexes($genZipSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        if (!$this->sittingManager->isAlreadySent($sitting)) {
            return;
        }

        try {
            $this->timestampManager->createModifiedSittingTimestamp($sitting);
        } catch (LsHorodatageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
