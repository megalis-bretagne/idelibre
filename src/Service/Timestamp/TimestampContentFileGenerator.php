<?php

namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TimestampContentFileGenerator
{
    public function __construct(
        private readonly Environment           $twig,
        private readonly ParameterBagInterface $bag,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * @param Convocation[] $convocations
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generateConvocationFile(Sitting $sitting, iterable $convocations): string
    {
        $txt = $this->twig->render('generate/sent_timestamp_template.txt.twig', [
            'sitting' => $sitting,
            'convocations' => $convocations,
        ]);

        $path = $this->getAndCreateTokenDirectory($sitting) . 'sendEmail_' . Uuid::uuid4();
        file_put_contents($path, $txt);

        return $path;
    }


    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function generateUpdatedConvocationFile(Sitting $sitting, Convocation $convocation): string
    {
        $txt = $this->twig->render('generate/updated_timestamp_template.txt.twig', [
            'sitting' => $sitting,
            'convocation' => $convocation,
        ]);

        $path = $this->getAndCreateTokenDirectory($sitting) . 'reSendEmail_' . Uuid::uuid4();
        file_put_contents($path, $txt);

        return $path;
    }

    public function generateModifiedSittingFile(Sitting $sitting): string
    {
        $txt = $this->twig->render('generate/modify_Sitting_timestamp_template.txt.twig', [
            'sitting' => $sitting,
        ]);

        $path = $this->getAndCreateTokenDirectory($sitting) . 'sittingModified_' . Uuid::uuid4();
        file_put_contents($path, $txt);

        return $path;
    }

    private function getAndCreateTokenDirectory(Sitting $sitting): string
    {
        $year = $sitting->getDate()->format('Y');
        $tokenDirectory = "{$this->bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}/";
        $this->filesystem->mkdir($tokenDirectory);

        return $tokenDirectory;
    }

    public function generateConvocationReceivedFile(Sitting $sitting, Convocation $convocation): string
    {
        $txt = $this->twig->render('generate/received_timestamp_template.txt.twig', [
            'sitting' => $sitting,
            'convocation' => $convocation,
        ]);

        $path = $this->getAndCreateTokenDirectory($sitting) . 'read_' . Uuid::uuid4();
        file_put_contents($path, $txt);

        return $path;
    }
}
