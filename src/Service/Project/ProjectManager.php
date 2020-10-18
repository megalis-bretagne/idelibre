<?php


namespace App\Service\Project;


use App\Entity\Sitting;
use App\Service\ClientEntity\ClientProject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectManager
{
    /**
     * @param ClientProject[] $clientProject
     * @param UploadedFile[] $files
     */
    public function update(array $clientProject, array $files, Sitting $sitting)
    {
    }
}
