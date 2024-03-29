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
        private Environment $twig,
        private ParameterBagInterface $bag,
        private Filesystem $filesystem
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

        $path = $this->getAndCreateTokenDirectory($sitting) . Uuid::uuid4();
        file_put_contents($path, $txt);

        return $path;
    }

    public function generateModifiedSittingFile(Sitting $sitting): string
    {
        $txt = $this->twig->render('generate/modify_Sitting_timestamp_template.txt.twig', [
            'sitting' => $sitting,
        ]);

        $path = $this->getAndCreateTokenDirectory($sitting) . 'modified_' . Uuid::uuid4();
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
}
