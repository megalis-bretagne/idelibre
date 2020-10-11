<?php


namespace App\Service\Seance;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class SittingDto
{
    private string $id;
    private string $name;
    private \DateTimeInterface $date;
    private UploadedFile $convocationFile;


}
