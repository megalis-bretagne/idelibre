<?php

namespace App\Controller;

use App\Service\File\FileManager;
use App\Service\ImageHandler\UploadStorageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/tinymce-upload")]
class TinyMceUploadController extends AbstractController
{
    public const MAX_FILESIZE = 200000; // 200 kB

    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly UploadStorageHandler $uploadStorageHandler,
        private readonly FileManager $fileManager
    ) {
    }


    #[Route("/image", name: "api_tinymce_upload_image")]
    public function upload(Request $request): Response
    {
        $structure = $this->getUser()->getStructure();

        // @TODO: Set your own domain(s) in `$allowedOrigins`  Dans un voter
        $allowedOrigins = ["https://localhost", $this->bag->get('base_url')];
        $origin = $request->server->get('HTTP_ORIGIN');

        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if ($origin && !in_array($origin, $allowedOrigins)) {
            return new Response("Vous n'avez pas la permission d'accéder à cette ressource.", 403);
        }

        // Don't attempt to process the upload on an OPTIONS request
        if ($request->isMethod("OPTIONS")) {
            return new Response("", 200, ["Access-Control-Allow-Methods" => "POST, OPTIONS"]);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get("file");

        if (!$file) {
            return new Response("Missing file.", 400);
        }

        if ($file->getSize() > self::MAX_FILESIZE) {
            return new Response("Le poids maximal de l'image doit être de 200Kb : " . (self::MAX_FILESIZE / 1000000) . "MB", 400);
        }

        if (!str_starts_with($file->getMimeType(), "image/")) {
            return new Response("Le fichier doit être au format jpeg,jpg ou png.", 400);
        }

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
