<?php

namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class TimestampConvocation
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TimestampContentFileGenerator $contentGenerator,
        private readonly LshorodatageInterface $lshorodatage,
        private readonly TimestampManager $timestampManager,
    ) {
    }


    /**
     * @param Convocation[] $convocations
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LsHorodatageException
     */
    public function createConvocationTimestamp(Sitting $sitting, iterable $convocations): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateConvocationFile($sitting, $convocations));

        $tsTokenStream = $this->lshorodatage->createTimestampToken($timeStamp->getFilePathContent());
        $timeStamp->setFilePathTsa($this->timestampManager->saveTimestampInFile($tsTokenStream, $timeStamp->getFilePathContent()));

        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }


    /**
     * @throws SyntaxError
     * @throws LsHorodatageException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function createSendOrResendTimestamp(Sitting $sitting, Convocation $convocation): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateUpdatedConvocationFile($sitting, $convocation));

        $tsTokenStream = $this->lshorodatage->createTimestampToken($timeStamp->getFilePathContent());
        $timeStamp->setFilePathTsa($this->timestampManager->saveTimestampInFile($tsTokenStream, $timeStamp->getFilePathContent()));

        $convocation->setSentTimestamp($timeStamp);

        $this->em->persist($convocation);
        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }

    /**
     * @throws LsHorodatageException
     */
    public function createConvocationReceivedTimestamp(convocation $convocation): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateConvocationReceivedFile($convocation->getSitting(), $convocation));

        $tsTokenStream = $this->lshorodatage->createTimestampToken($timeStamp->getFilePathContent());
        $timeStamp->setFilePathTsa($this->timestampManager->saveTimestampInFile($tsTokenStream, $timeStamp->getFilePathContent()));

        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }
}
