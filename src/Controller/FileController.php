<?php

namespace App\Controller;

use App\Entity\File;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route(path: '/file/download/{id}', name: 'file_download', methods: ['GET'])]
    #[IsGranted( 'DOWNLOAD_FILES', subject: 'file')]
    public function download(File $file): Response
    {
        $response = new BinaryFileResponse($file->getPath());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getName()
        );
        $response->headers->set('X-Accel-Redirect', $file->getPath());

        return $response;
    }
}
