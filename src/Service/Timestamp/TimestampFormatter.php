<?php


namespace App\Service\Timestamp;

use App\Entity\Convocation;
use App\Entity\Sitting;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TimestampFormatter
{
    private Environment $twig;
    private ParameterBagInterface $bag;

    public function __construct(Environment $twig, ParameterBagInterface $bag)
    {
        $this->twig = $twig;
        $this->bag = $bag;
    }

    /**
     * @param Convocation[] $convocations
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generate(Sitting $sitting, iterable $convocations): string
    {
        $txt = $this->twig->render('generate/sent_timestamp_template.txt.twig', [
            'sitting' => $sitting,
            'convocations' => $convocations
        ]);

        $path = "{$sitting->getStructure()->getId()}/{$sitting->getId()}/" . Uuid::uuid4() ;

        file_put_contents($path, $txt);

        return $txt;
    }
}
