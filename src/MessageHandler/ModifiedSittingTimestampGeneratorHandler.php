<?php

namespace App\MessageHandler;

use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Seance\SittingManager;
use App\Service\Timestamp\TimestampSitting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ModifiedSittingTimestampGeneratorHandler
{
    public function __construct(
        private readonly SittingRepository      $sittingRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger,
        private readonly SittingManager $sittingManager,
        private readonly TimestampSitting $timestampSitting,
    ) {
    }


    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(UpdatedSitting $genZipSitting): void
    {
        $this->em->clear();
        $sitting = $this->sittingRepository->findWithProjectsAnnexesAndOtherDocs($genZipSitting->getSittingId());
        if (!$sitting) {
            throw new NotFoundHttpException('the sitting with id ' . $genZipSitting->getSittingId() . 'does not exists');
        }

        if (!$this->sittingManager->isAlreadySent($sitting)) {
            return;
        }

        try {
            $this->timestampSitting->createModifiedSittingTimestamp($sitting);
        } catch (LsHorodatageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
