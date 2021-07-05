<?php

namespace App\Service\LegacyWs;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Type\TypeManager;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LegacyWsService
{
    private TypeManager $typeManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    private ConvocationManager $convocationManager;
    private SittingRepository $sittingRepository;
    private WsActorManager $wsActorManager;
    private WsProjectManager $wsProjectManager;

    public function __construct(
        EntityManagerInterface $em,
        TypeManager $typeManager,
        FileManager $fileManager,
        ConvocationManager $convocationManager,
        SittingRepository $sittingRepository,
        WsActorManager $wsActorManager,
        WsProjectManager $wsProjectManager
    ) {
        $this->typeManager = $typeManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->convocationManager = $convocationManager;
        $this->sittingRepository = $sittingRepository;
        $this->wsActorManager = $wsActorManager;
        $this->wsProjectManager = $wsProjectManager;
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    public function createSitting(array $rawSitting, array $uploadedFiles, Structure $structure): Sitting
    {
        $this->em->getConnection()->beginTransaction();
        $this->validateRawSitting($rawSitting, $uploadedFiles, $structure);
        $sitting = new Sitting();
        $this->em->persist($sitting);

        $date = new \DateTime($rawSitting['date_seance'], new DateTimeZone($structure->getTimezone()->getName()));
        $date = $date->setTimezone(new DateTimeZone('UTC'));

        $type = $this->typeManager->getOrCreateType($rawSitting['type_seance'], $structure);

        $this->associateActorsToType($type, $rawSitting['acteurs_convoques'] ?? null);

        $convocationFile = $this->fileManager->save($uploadedFiles['convocation'], $structure);

        $sitting->setStructure($structure)
            ->setType($type)
            ->setDate($date)
            ->setName($type->getName())
            ->setConvocationFile($convocationFile);

        $this->wsProjectManager->createProjectsAndAnnexes($rawSitting['projets'] ?? null, $uploadedFiles, $sitting);

        if (isset($rawSitting['place'])) {
            $sitting->setPlace($rawSitting['place']);
        }

        $this->convocationManager->createConvocationsActors($sitting);

        $this->em->flush();

        $this->em->getConnection()->commit();

        return $sitting;
    }

    private function associateActorsToType($type, ?string $rawActors): void
    {
        if (empty($rawActors)) {
            return;
        }

        $wsActors = $this->wsActorManager->validateAndFormatActor(json_decode($rawActors, true));
        if (!empty($wsActors)) {
            $this->wsActorManager->associateActorsToType($type, $wsActors);
        }
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function validateRawSitting(array $rawSitting, array $uploadedFiles, Structure $structure): void
    {
        if (!isset($rawSitting['date_seance'])) {
            throw new BadRequestHttpException('date_seance is required');
        }

        if (!isset($rawSitting['type_seance'])) {
            throw new BadRequestHttpException('type_seance is required');
        }

        if (!isset($uploadedFiles['convocation'])) {
            throw new BadRequestHttpException('convocation file is required');
        }

        if (!empty($rawSitting['acteurs_convoques'])) {
            try {
                json_decode($rawSitting['acteurs_convoques']);
            } catch (\Exception $e) {
                throw new BadRequestHttpException('acteurs_convoques must be json format');
            }
        }

        if ($this->isAlreadyExistsSitting($rawSitting, $structure)) {
            throw new BadRequestHttpException('sitting same type same datetime already exists');
        }
    }

    private function isAlreadyExistsSitting(array $rawSitting, Structure $structure): ?Sitting
    {
        return $this->sittingRepository->findOneBy([
            'name' => $rawSitting['type_seance'],
            'date' => new DateTimeImmutable($rawSitting['date_seance']),
            'structure' => $structure,
        ]);
    }
}
