<?php

namespace App\Controller;

use App\Service\UploadStorageHandler\UploadStorageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TinyMceUploadController extends AbstractController
{
    const MAX_FILESIZE = 200000; // 200 kB

    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly UploadStorageHandler $uploadStorageHandler
    )
    {
    }


    #[Route("/api/tinymce-upload/image", name: "api_tinymce_upload_image")]
    public function upload(Request $request): Response
    {
        // @TODO: Set your own domain(s) in `$allowedOrigins`
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
            $this->addFlash("error", "Le poids maximal de l'image doit être de 200Kb : " . (self::MAX_FILESIZE / 1000000) . "MB");
            return new Response("Le poids maximal de l'image doit être de 200Kb : " . (self::MAX_FILESIZE / 1000000) . "MB", 400);
        }

        if (!str_starts_with($file->getMimeType(), "image/")) {
            return new Response("Le fichier doit être au format jpeg,jpg ou png.", 400);
        }


        $structure = $this->getUser()->getStructure();
        $fileName = 'image' . uniqid() . '.' . $file->guessExtension();
        $this->uploadStorageHandler->upload($file, $structure, $fileName);

        $location = $this->generateUrl('serve_image', ['fileName' =>  $fileName , 'structureId' => $structure->getId()]);

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


    #[Route("/api/tinymce-upload/serveImage/{fileName}/{structureId}", name: "serve_image")]
    public function getImageTinyMce(string $fileName, string $structureId): Response
    {
        $file = file_get_contents('/data/image/' . $structureId . '/emailTemplateImages/' . $fileName);
        $response = new Response($file);
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }

}
