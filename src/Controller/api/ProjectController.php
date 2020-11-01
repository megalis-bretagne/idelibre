<?php


namespace App\Controller\api;

use App\Entity\Sitting;
use App\Message\GenZipSitting;
use App\Service\ApiEntity\ProjectApi;
use App\Service\Project\ProjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectController extends AbstractController
{

    /**
     * @Route("/api/projects/{id}", name="api_project_add", methods={"POST"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function edit(Sitting $sitting, Request $request, SerializerInterface $serializer, ProjectManager $projectManager, MessageBusInterface $messageBus): JsonResponse
    {
        $rawProjects = $request->request->get('projects');
        $projects = $serializer->deserialize($rawProjects, ProjectApi::class . '[]', 'json');

        $projectManager->update($projects, $request->files->all(), $sitting);

        $messageBus->dispatch(new GenZipSitting($sitting->getId()));

        return $this->json(['reception' => 'ok']);
    }

    /**
     * @Route("/api/projects/{id}", name="api_project_get", methods={"GET"})
     * @IsGranted("MANAGE_SITTINGS", subject="sitting")
     */
    public function getProjectsFromSitting(Sitting $sitting, SerializerInterface $serializer, ProjectManager $projectManager): JsonResponse
    {
        $projectsApi = $projectManager->getApiProjectsFromProjects($projectManager->getProjectsFromSitting($sitting));

        return $this->json($projectsApi);
    }
}
