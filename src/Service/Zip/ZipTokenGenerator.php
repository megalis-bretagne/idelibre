<?php

namespace App\Service\Zip;

use App\Entity\Sitting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ZipArchive;

class ZipTokenGenerator
{
    public function __construct(private ParameterBagInterface $bag)
    {
    }

    public function generateZipToken(Sitting $sitting): string
    {
        $tmpPath = '/tmp/' . uniqid('zip_token');
        $zip = new ZipArchive();
        $zip->open($tmpPath, ZipArchive::CREATE);
        $zip->addGlob($this->getTimestampDirectory($sitting) . '*', 0, ['remove_all_path' => true, 'add_path' => 'jetons/']);
        $zip->close();

        return $tmpPath;
    }

    private function getTimestampDirectory(Sitting $sitting): string
    {
        $year = $sitting->getDate()->format('Y');

        return "{$this->bag->get('token_directory')}{$sitting->getStructure()->getId()}/$year/{$sitting->getId()}/";
    }
}
