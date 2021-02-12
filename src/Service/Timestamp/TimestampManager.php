<?php

namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Psr\Http\Message\StreamInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TimestampManager
{
    private EntityManagerInterface $em;
    private TimestampContentFileGenerator $contentGenerator;
    private LshorodatageInterface $lshorodatage;


    public function __construct(
        EntityManagerInterface $em,
        TimestampContentFileGenerator $contentGenerator,
        LshorodatageInterface $lshorodatage
    )
    {
        $this->em = $em;
        $this->contentGenerator = $contentGenerator;
        $this->lshorodatage = $lshorodatage;
    }

    public function delete(Timestamp $timestamp): void
    {
        $this->em->remove($timestamp);
    }

    /**
     * @param Convocation[] $convocations
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LsHorodatageException
     */
    public function createTimestamp(Sitting $sitting, iterable $convocations): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateFile($sitting, $convocations));

        $tsTokenStream = $this->lshorodatage->createTimestampToken($timeStamp->getFilePathContent());
        $timeStamp->setFilePathTsa($this->saveTimestampInFile($tsTokenStream, $timeStamp->getFilePathContent()));

        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }


    private function saveTimestampInFile(StreamInterface $tsToken, string $path): string
    {
        $pathTsa = $path . '.tsa';
        file_put_contents($pathTsa, $tsToken);

        return $pathTsa;
    }
}
