<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\File\FileManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route(path: '/file/download/{id}', name: 'file_download', methods: ['GET'])]
    #[IsGranted(data: 'DOWNLOAD_FILES', subject: 'file')]
    public function download(File $file, FileManager $fileManager): Response
    {
        $filePath = $file->getPath();

        if (null === $file->getCachedAt() || false === $fileManager->fileExist($filePath)) {
            if (false === $fileManager->downloadToS3($filePath)) {
                throw new NotFoundHttpException("file not found : $filePath");
            }

            $fileManager->updateCachedAt($file);
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getName()
        );
        $response->headers->set('X-Accel-Redirect', $filePath);

        return $response;
    }
}
