<?php

namespace App\Service\Timestamp;

use App\Entity\Sitting;
use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;

class TimestampSitting
{
    public function __construct(
        private readonly EntityManagerInterface        $em,
        private readonly TimestampContentFileGenerator $contentGenerator,
        private readonly LshorodatageInterface         $lshorodatage,
        private readonly TimestampManager              $timestampManager,
    ) {
    }



    /**
     * @throws LsHorodatageException
     */
    public function createModifiedSittingTimestamp(Sitting $sitting): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateModifiedSittingFile($sitting));

        $tsTokenStream = $this->lshorodatage->createTimestampToken($timeStamp->getFilePathContent());
        $timeStamp->setFilePathTsa($this->timestampManager->saveTimestampInFile($tsTokenStream, $timeStamp->getFilePathContent()));

        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }
}
