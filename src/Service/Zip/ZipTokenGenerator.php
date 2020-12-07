<?php


namespace App\Service\Zip;

use App\Entity\Sitting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ZipArchive;

class ZipTokenGenerator
{
    private ParameterBagInterface $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    public function generateZipToken(Sitting $sitting)
    {
        $tmpPath = "/tmp/" . uniqid("zip_token");
        $zip = new ZipArchive();
        $zip->open($tmpPath, ZipArchive::CREATE);
        $zip->addGlob($this->getTimestampDirectory($sitting) . '*');
        $zip->close();

        return $tmpPath;
    }

    private function getTimestampDirectory(Sitting $sitting)
    {
        $year = $sitting->getDate()->format("Y");
        return "{$this->bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}/";
    }
}
