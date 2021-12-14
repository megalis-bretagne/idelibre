<?php

namespace App\Controller\Api32;

use App\Entity\Annex;
use App\Entity\Convocation;
use App\Entity\Project;
use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Service\LegacyWs\LegacyWsService;
use App\Service\Seance\SittingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class SittingController extends AbstractController
{
    /**
     * @deprecated
     * @Route("/api/v1/seances", name="list_sittings", methods={"GET"})
     */
    public function listSittings(
        Request $request,
        VerifyToken $verifyToken,
        SittingRepository $sittingRepository
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $sittings = $sittingRepository->findBy(['structure' => $structure]);

        $formattedSittings = [];
        foreach ($sittings as $sitting) {
            $formattedSittings[] = [
                'id' => $sitting->getId(),
                'name' => $sitting->getName(),
                'type_id' => $sitting->getType() ? $sitting->getType()->getId() : null,
                'archive' => $sitting->getIsArchived(),
            ];
        }

        return $this->json($formattedSittings);
    }

    /**
     * @deprecated
     * @Route("/api/v1/seances/{id}", name="detail_sitting", methods={"GET"})
     */
    public function detailSitting(
        string $id,
        Request $request,
        VerifyToken $verifyToken,
        SittingRepository $sittingRepository
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $sitting = $sittingRepository->findWithFullDetail($id, $structure);

        if (!$sitting) {
            return $this->json('msg => not found', 404);
        }

        $formattedConvocations = [];
        foreach ($sitting->getConvocations() as $convocation) {
            $formattedConvocations[] = $this->formatConvocation($convocation);
        }

        $formattedProjects = [];
        foreach ($sitting->getProjects() as $project) {
            $formattedProjects[] = $this->formatProject($project);
        }

        $formatted = [
            'id' => $sitting->getId(),
            'name' => $sitting->getName(),
            'date_seance' => $sitting->getDate(),
            'type_id' => $sitting->getType() ? $sitting->getType()->getId() : null,
            'archive' => $sitting->getIsArchived(),
            'convocations' => $formattedConvocations,
            'projets' => $formattedProjects,
        ];

        return $this->json($formatted);
    }

    /**
     * @deprecated
     * @Route("/api/v1/seances/{id}", name="delete_sitting", methods={"DELETE"})
     */
    public function deleteSitting(
        string $id,
        Request $request,
        VerifyToken $verifyToken,
        SittingRepository $sittingRepository,
        SittingManager $sittingManager
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $sitting = $sittingRepository->findOneBy(['id' => $id, 'structure' => $structure]);

        if (!$sitting) {
            return $this->json('msg => not found', 404);
        }

        $sittingManager->delete($sitting);

        return $this->json(null, 204);
    }

    /**
     * @deprecated
     * @Route("/api/v1/seances", name="create_sitting", methods={"POST"})
     */
    public function addSitting(
        Request $request,
        VerifyToken $verifyToken,
        LegacyWsService $legacyWsService
    ): JsonResponse {
        $structure = $verifyToken->validate($request);
        $jsonData = $request->request->get('seance');
        $rawSitting = json_decode($jsonData, true);
        if (!$rawSitting) {
            throw new BadRequestHttpException('jsonData is not a valid json');
        }

        $rawSitting = $this->addTypeSeance($rawSitting);
        $rawSitting = $this->AddOrdreToAnnex($rawSitting);

        try {
            $sitting = $legacyWsService->createSitting($rawSitting, $request->files->all(), $structure);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'code' => 'Seance.add.error',
                'message' => $e->getMessage(),
            ], 400);
        }

        return $this->json(['id' => $sitting->getId()], 201);
    }

    private function addTypeSeance(array $rawSitting)
    {
        $rawSitting['type_seance'] = $rawSitting['type_name'];

        return $rawSitting;
    }

    private function AddOrdreToAnnex(array $rawSitting)
    {
        foreach ($rawSitting['projets'] as &$rawProject) {
            foreach ($rawProject['annexes'] as &$rawAnnex) {
                $rawAnnex['ordre'] = $rawAnnex['rank'];
            }
        }

        return $rawSitting;
    }

    private function formatConvocation(Convocation $convocation): array
    {
        return [
            'id' => $convocation->getId(),
            'read' => $convocation->getIsRead(),
            'presentstatus' => $convocation->getAttendance(),
            'ae_horodatage' => $convocation->getSentTimestamp() ? $convocation->getSentTimestamp()->getCreatedAt() : null,
            'ar_horodatage' => $convocation->getReceivedTimestamp() ? $convocation->getReceivedTimestamp()->getCreatedAt() : null,
            'procuration_name' => $convocation->getDeputy() ?? null,
            'user_id' => $convocation->getUser()->getId(),
            'seance_id' => $convocation->getSitting()->getId(),
            'user' => [
                'id' => $convocation->getUser()->getId(),
                'username' => $convocation->getUser()->getUsername(),
                'firstname' => $convocation->getUser()->getFirstName(),
                'lastname' => $convocation->getUser()->getLastName(),
                'mail' => $convocation->getUser()->getEmail(),
            ],
        ];
    }

    private function formatProject(Project $project): array
    {
        $formattedAnnexes = [];
        foreach ($project->getAnnexes() as $annex) {
            $formattedAnnexes[] = $this->formatAnnex($annex);
        }

        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'theme' => $project->getTheme() ? $project->getTheme()->getName() : null,
            'ptheme_id' => $project->getTheme() ? $project->getTheme()->getId() : null,
            'rank' => $project->getRank(),
            'seance_id' => $project->getSitting()->getId(),
            'annexes' => $formattedAnnexes,
        ];
    }

    private function formatAnnex(Annex $annex): array
    {
        return [
            'id' => $annex->getId(),
            'name' => $annex->getFile()->getName(),
            'rank' => $annex->getRank(),
            'projet_id' => $annex->getProject()->getId(),
        ];
    }

    private function createSitting(?string $typeId, ?string $dateSitting, array $projects, array $uploadedFiles, Structure $structure)
    {
        $this->em->getConnection()->beginTransaction();
    }
}
