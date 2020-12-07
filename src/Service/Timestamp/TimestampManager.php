<?php


namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TimestampManager
{
    private EntityManagerInterface $em;
    private TimestampContentFileGenerator $contentGenerator;
    private TimestampServiceInterface $timestampService;

    public function __construct(
        EntityManagerInterface $em,
        TimestampContentFileGenerator $contentGenerator,
        TimestampServiceInterface $timestampService
    ) {
        $this->em = $em;
        $this->contentGenerator = $contentGenerator;
        $this->timestampService = $timestampService;
    }

    public function delete(Timestamp $timestamp): void
    {
        $this->em->remove($timestamp);
    }

    /**
     * @param Convocation[] $convocations
     * @return Timestamp
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createTimestamp(Sitting $sitting, iterable $convocations): Timestamp
    {
        $timeStamp = new Timestamp();
        $timeStamp->setFilePathContent($this->contentGenerator->generateFile($sitting, $convocations));
        $timeStamp->setFilePathTsa($this->timestampService->signTimestamp($timeStamp));
        $this->em->persist($timeStamp);
        $this->em->flush();

        return $timeStamp;
    }
}
