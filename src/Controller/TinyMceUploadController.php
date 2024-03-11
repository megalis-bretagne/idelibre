<?php

namespace App\Controller;

use App\Service\File\FileManager;
use App\Service\ImageHandler\imageUploadValidator;
use App\Service\ImageHandler\UploadStorageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/api/tinymce-upload")]
class TinyMceUploadController extends AbstractController
{

    public function __construct(
        private readonly UploadStorageHandler $uploadStorageHandler,
        private readonly FileManager $fileManager,
        private readonly imageUploadValidator $imageUploadValidator,
    ) {
    }


    #[Route("/image", name: "api_tinymce_upload_image")]
    #[IsGranted('ROLE_MANAGE_EMAIL_TEMPLATES')]
    public function upload(Request $request): Response
    {
        $structure = $this->getUser()->getStructure();
        $origin = $request->server->get('HTTP_ORIGIN');

        if ($request->isMethod("OPTIONS")) {
            return new Response("", 200, ["Access-Control-Allow-Methods" => "POST, OPTIONS"]);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get("file");

        $this->imageUploadValidator->isMissingFile($file);
        $this->imageUploadValidator->isTooBig($file);
        $this->imageUploadValidator->isNotImage($file);

        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $filename = $this->fileManager->saveTmpImage($structure, $extension);
        $this->uploadStorageHandler->upload($file, $structure, $filename);

        $location = $this->generateUrl('serve_image', ['structureId' => $structure->getId(), 'fileName' => $filename]);


        return new JsonResponse(
            ["location" => $location],
            200,
            [
                "Access-Control-Allow-Origin" => $origin,
                "Access-Control-Allow-Credentials" => true,
                "P3P" => 'CP="There is no P3P policy."',
            ],
        );
    }


    #[Route("/serve-image/{structureId}/{fileName}", name: "serve_image", methods: ["GET"])]
    public function getImageTinyMce(string $fileName, string $structureId): Response
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $tmpPath = '/tmp/image/' . $structureId . '/' . $fileName;
        $truePath = '/data/image/' . $structureId . '/' . $fileName;

        if (file_exists($truePath)) {
            $file = file_get_contents($truePath);
            return new Response($file, 200, ['Content-Type' => 'image/' . $extension]);
        }

        $file = file_get_contents($tmpPath);
        return new Response($file, 200, ['Content-Type' => 'image/' . $extension]);
    }
}
