<?php

namespace App\Controller\ApiV2;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Message\UpdatedSitting;
use App\Repository\ConvocationRepository;
use App\Repository\ProjectRepository;
use App\Repository\SittingRepository;
use App\Security\Http400Exception;
use App\Service\ApiEntity\ProjectApi;
use App\Service\Pdf\PdfValidator;
use App\Service\Project\ProjectManager;
use App\Service\Seance\SittingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v2/structures/{structureId}/sittings')]
#[ParamConverter('structure', class: Structure::class, options: ['id' => 'structureId'])]
#[IsGranted('API_AUTHORIZED_STRUCTURE', subject: 'structure')]
class SittingApiController extends AbstractController
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private MessageBusInterface   $messageBus,
        private PdfValidator          $pdfValidator,
    )
    {
    }

    #[Route('', name: 'get_all_sittings', methods: ['GET'])]
    public function getAll(
        Structure         $structure,
        Request           $request,
        SittingRepository $sittingRepository
    ): JsonResponse
    {
        $sittings = $sittingRepository->findByStructure($structure, null, $request->query->get('status'))
            ->getQuery()->getResult();

        return $this->json($sittings, context: ['groups' => 'sitting:read']);
    }

    #[Route('/{id}', name: 'get_one_sitting', methods: ['GET'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getById(
        Structure $structure,
        Sitting   $sitting
    ): JsonResponse
    {
        return $this->json($sitting, context: ['groups' => ['sitting:detail', 'sitting:read']]);
    }

    #[Route('/{sittingId}/convocations', name: 'get_all_convocations_by_sitting', methods: ['GET'])]
    #[ParamConverter('sitting', class: Sitting::class, options: ['id' => 'sittingId'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getAllConvocations(
        Structure             $structure,
        Sitting               $sitting,
        ConvocationRepository $convocationRepository
    ): JsonResponse
    {
        $convocations = $convocationRepository->getConvocationsWithUserBySitting($sitting);

        return $this->json($convocations, context: ['groups' => 'convocation:read']);
    }

    #[Route('/{sittingId}/projects', name: 'get_all_projects_by_sitting', methods: ['GET'])]
    #[ParamConverter('sitting', class: Sitting::class, options: ['id' => 'sittingId'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function getAllProjects(
        Structure         $structure,
        Sitting           $sitting,
        ProjectRepository $projectRepository
    ): JsonResponse
    {
        $projects = $projectRepository->getProjectsBySitting($sitting);

        return $this->json($projects, context: ['groups' => 'project:read']);
    }

    #[Route('', name: 'add_sitting', methods: ['POST'])]
    public function addSitting(
        Structure      $structure,
        Request        $request,
        SittingManager $sittingManager
    )
    {
        $context = ['groups' => ['sitting:write', 'sitting:write:post'], 'normalize_relations' => true];
        /** @var Sitting $sitting */
        $sitting = $this->denormalizer->denormalize($request->request->all(), Sitting::class, context: $context);

        if (!$request->files->get('convocationFile')) {
            throw new Http400Exception('File with key convocationFile is required');
        }

        $sittingManager->save(
            $sitting,
            $request->files->get('convocationFile'),
            $request->files->get('invitationFile') ?? null,
            $structure
        );

        return $this->json($sitting, context: ['groups' => ['sitting:detail', 'sitting:read']]);
    }

    #[Route('/{id}', name: 'update_sitting', methods: ['PUT'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function updateSitting(
        Structure         $structure,
        Sitting           $sitting,
        Request           $request,
        SittingManager    $sittingManager,
        SittingRepository $sittingRepository
    )
    {
        $context = ['object_to_populate' => $sitting, 'groups' => ['sitting:write']];

        /** @var Sitting $sitting */
        $sitting = $this->denormalizer->denormalize($request->request->all(), Sitting::class, context: $context);

        $sittingManager->update(
            $sitting,
            $request->files->get('convocationFile') ?? null,
            $request->files->get('invitationFile') ?? null
        );

        $updatedSitting = $sittingRepository->find($sitting->getId());

        return $this->json($updatedSitting, context: ['groups' => ['sitting:detail', 'sitting:read']]);
    }


    #[Route('/{sittingId}/projects', name: 'add_projects_to_sitting', methods: ['POST'])]
    #[ParamConverter('sitting', class: Sitting::class, options: ['id' => 'sittingId'])]
    #[IsGranted('API_SAME_STRUCTURE', subject: ['structure', 'sitting'])]
    public function addProjectsToSitting(
        Structure           $structure,
        Sitting             $sitting,
        Request             $request,
        SerializerInterface $serializer,
        ProjectManager      $projectManager,
        ProjectRepository   $projectRepository,
        SittingManager      $sittingManager

    ): JsonResponse
    {
        if (count($sitting->getProjects())) {
            throw new Http400Exception("Sitting already contain projects");
        }

        if($sittingManager->isAlreadySent($sitting)) {
            throw new Http400Exception("Sitting is already sent");
        }

        $rawProjects = $request->get('projects');
        $projects = $serializer->deserialize($rawProjects, ProjectApi::class . '[]', 'json');
        if (!$this->pdfValidator->isProjectsPdf($projects)) {
            return $this->json(['success' => false, 'message' => 'Au moins un projet n\'est pas un pdf'], 400);
        }

        $projectManager->update($projects, $request->files->all(), $sitting);

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));

        $updated = $projectRepository->getProjectsBySitting($sitting);

        return $this->json($updated, status: 201, context: ['groups' => 'project:read']);
    }
}
