<?php


namespace App\Controller\api;


use App\Entity\Sitting;
use App\Service\ClientEntity\ClientAnnex;
use App\Service\ClientEntity\ClientProject;
use App\Service\Project\ProjectManager;
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
     * @Route("/api/check", name="api_check", methods={"GET"})
     */
    public function check(SerializerInterface $serializer)
    {
        $annex1 = new ClientAnnex();
        $annex1->linkedFile='mnFichierAnnex';

        $annex2 = new ClientAnnex();
        $annex2->linkedFile='Un autre fichier annex';

        $projet = new ClientProject();
        $projet->setName('premier')
            ->setRapporteurId('azazaz')
            ->setThemeId('rerere')
            ->setAnnexes([$annex1, $annex2]);

        $projet2 = new ClientProject();
        $projet2->setName('deuxieme');

        $projects = [$projet, $projet2];

        $serialized = $serializer->serialize($projects, 'json');



        $clientProject = $serializer->deserialize($serialized, ClientProject::class .'[]', 'json');

        dd($clientProject);

    }

    /**
     * @Route("/api/projects/{id}", name="api_project_add", methods={"POST"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function add(Sitting $sitting, Request $request, SerializerInterface $serializer, ProjectManager $projectManager): Response
    {
        $rawProjects = $request->request->get('projects');
        $projects = $serializer->deserialize($rawProjects, ClientProject::class . '[]', 'json');

        $projectManager->update($projects, $request->files->all(), $sitting);



        return $this->json(['reception' => 'ok']);
    }
}
