<?php


namespace App\Controller\api;


use App\Service\ClientEntity\ClientProject;
use App\Service\Theme\ThemeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectController extends AbstractController
{
    /**
     * @Route("/api/projects", name="api_project_add", methods={"POST"})
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function add(Request $request, SerializerInterface $serializer): Response
    {
        $rawProjects = $request->request->get('projects');
        /** @var UploadedFile[] $files */
        //$files = $request->files->all();
        //dump($rawProjects);
       // foreach ($files as $file) {
         //   dump($file);
       // }

        //$projects = $serializer->deserialize($rawProjects, ClientProject::class . '[]', 'json');
        //dump($projects);

        return $this->json(['reception' => 'ok']);
    }
}
