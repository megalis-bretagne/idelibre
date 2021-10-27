<?php

namespace App\Service\LegacyWs;

use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;

class WsActorManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private RoleManager $roleManager,
        private ActorFinder $actorFinder
    ) {
    }

    /**
     * @param WsActor[] $wsActors
     */
    public function associateActorsToType(Type $type, array $wsActors): void
    {
        $associatedActors = $this->userRepository->getAssociatedActorsWithType($type);
        if (empty($wsActors)) {
            return;
        }
        $uniqWsActors = $this->removeDuplicate($wsActors);
        $wsActorsToAdd = $this->getAddedActors($associatedActors, $uniqWsActors);
        $this->addWsActorsToType($wsActorsToAdd, $type);
    }

    /**
     * @param WsActor[] $wsActors
     */
    private function addWsActorsToType(array $wsActors, Type $type)
    {
        foreach ($wsActors as $wsActor) {
            $actorUsername = $this->generateUserName($wsActor->firstName, $wsActor->lastName, $type->getStructure()->getSuffix());
            $existingActor = $this->actorFinder->findByStructure($wsActor, $type->getStructure(), $actorUsername);

            if ($existingActor) {
                $type->addAssociatedUser($existingActor);
                continue;
            }

            $newActor = $this->createActorFromWsActor($wsActor, $type->getStructure());
            $type->addAssociatedUser($newActor);
        }
    }

    private function createActorFromWsActor(WsActor $wsActor, Structure $structure): User
    {
        $actor = (new User())
            ->setStructure($structure)
            ->setPassword('NotInitialiazed')
            ->setEmail($wsActor->email)
            ->setFirstName($wsActor->firstName)
            ->setLastName($wsActor->lastName)
            ->setUsername($this->generateUserName($wsActor->firstName, $wsActor->lastName, $structure->getSuffix()))
            ->setTitle($wsActor->title)
            ->setRole($this->roleManager->getActorRole())
            ->setIsActive(true);

        $this->em->persist($actor);

        return $actor;
    }

    private function generateUserName(string $firstName, string $lastName, string $suffix): string
    {
        $normalizedFirstnameLetter = $this->stringNormalizer($firstName[0]);
        $normalizedLastName = $this->stringNormalizer($lastName);

        return "${normalizedFirstnameLetter}.${normalizedLastName}@${suffix}";
    }

    private function stringNormalizer(string $text): string
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @param User[]    $associatedActors
     * @param WsActor[] $wsActors
     */
    private function getAddedActors(iterable $associatedActors, array $wsActors): array
    {
        $addedWsActors = [];
        foreach ($wsActors as $wsActor) {
            foreach ($associatedActors as $associatedActor) {
                if (strtolower($wsActor->firstName) === strtolower($associatedActor->getFirstName())
                    && strtolower($wsActor->lastName) === strtolower($associatedActor->getLastName())) {
                    continue 2;
                }
            }
            $addedWsActors[] = $wsActor;
        }

        return $addedWsActors;
    }

    /**
     * @param WsActor[] $wsActors
     *
     * @return WsActor[]
     */
    private function removeDuplicate(array $wsActors): array
    {
        /** @var WsActor[] $uniqRawUsers */
        $uniqRawUsers = [];
        foreach ($wsActors as $wsActor) {
            foreach ($uniqRawUsers as $uniqUser) {
                if (strtolower($uniqUser->firstName) === strtolower($wsActor->firstName)
                    && strtolower($uniqUser->lastName) === strtolower($wsActor->lastName)) {
                    continue 2;
                }
            }
            $uniqRawUsers[] = $wsActor;
        }

        return $uniqRawUsers;
    }

    /**
     * @param ?array $rawActors
     *
     * @return ?WsActor[];
     */
    public function validateAndFormatActor(?array $rawActors): ?array
    {
        if (!$rawActors) {
            return null;
        }
        $wsActors = [];
        foreach ($rawActors as $rawActor) {
            $wsActors[] = new WsActor($rawActor);
        }

        return $wsActors;
    }
}
