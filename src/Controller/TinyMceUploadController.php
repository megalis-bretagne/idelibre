<?php

namespace App\Controller;

//use App\Storage\UserUploadStorage;
//use App\Controller\api\UserUploadStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TinyMceUploadController extends AbstractController
{
    const MAX_FILESIZE = 2000000; // 2 MB

    public function __construct(
        private readonly ParameterBagInterface $bag,
    )
    {
    }


    #[Route("/api/tinymce-upload/image", name:"api_tinymce_upload_image")]
    public function upload(Request $request, /*UserUploadStorage $userUploadStorage*/): Response
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
            return new Response("Your file is too big. Maximum size: ".(self::MAX_FILESIZE / 1000000)."MB", 400);
        }

        if (!str_starts_with($file->getMimeType(), "image/")) {
            return new Response("Le fichier doit être au format jpeg,jpg ou png.", 400);
        }

        /**
         * @TODO: Replace this next line with your own file upload/save process.
         * The $fileUrl variable should contain the publicly accessible URL of
         * the file/image.
         */

        $imgDirPath = '/data/maStructure/emailTemplateImages/' . $fil;
        if (!file_exists($imgDirPath)) {
            mkdir($imgDirPath, 0755, true);
        }
        file_put_contents($imgDirPath , file_get_contents($file->getClientOriginalName()));
//        $fileUrl = $userUploadStorage->upload($file->getContent());

        return new JsonResponse(
            ["location" => $imgDirPath],
            200,
            [
                "Access-Control-Allow-Origin" => $origin,
                "Access-Control-Allow-Credentials" => true,
                "P3P" => 'CP="There is no P3P policy."',
            ],
        );
    }
}
