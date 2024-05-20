<?php

namespace App\Service\RecapNotificationMail;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Service\RecapNotificationMail\Model\EmailRecapData;
use App\Service\RecapNotificationMail\Model\RecapSittingInfo;
use DateTime;

class RecapDataProvider
{
    public function __construct(
        private readonly StructureRepository   $structureRepository,
        private readonly UserRepository        $userRepository,
        private readonly SittingRepository     $sittingRepository,
        private readonly ConvocationRepository $convocationRepository
    ) {
    }


    /**
     * @return array<EmailRecapData>
     */
    public function getAllStructuresRecapNotifications(): array
    {
        $structures = $this->structureRepository->findBy(['isActive' => true]);

        $allStructuresNotifications = [];
        foreach ($structures as $structure) {
            $allStructuresNotifications = [...$allStructuresNotifications, ...$this->getStructureRecapNotifications($structure)];
        }

        return $allStructuresNotifications;
    }

    /**
     * @return array<EmailRecapData>
     */
    private function getStructureRecapNotifications(Structure $structure): array
    {
        $users = $this->userRepository->findSecretariesAndAdminByStructureWithMailsRecap($structure)->getQuery()->getResult();
        $sittings = $this->sittingRepository->findActiveSittingsAfterDateByStructure($structure, new DateTime('0 days'));

        $notificationsToSend = [];

        foreach ($users as $user) {
            $notificationsToSend[] = $this->getEmailRecapData($user, $sittings);
        }

        return $notificationsToSend;
    }

    /**
     * @param array<Sitting> $sittings
     */
    public function getEmailRecapData(User $user, array $sittings): EmailRecapData
    {
        $recapSittingInfo = [];
        foreach ($sittings as $sitting) {
            if ($this->isAuthorizedSittingType($sitting->getType(), $user)) {
                $recapSittingInfo[] = new RecapSittingInfo(
                    $this->convocationRepository->getConvocationsWithUserBySitting($sitting),
                    $sitting,
                    $sitting->getStructure()->getTimezone()->getName()
                );
            }
        }

        return new EmailRecapData($user, $recapSittingInfo, $user->getStructure());
    }

    private function isAuthorizedSittingType(Type $type, User $user): bool
    {
        return $user->getRole()->getName() === "Admin" or $user->getAuthorizedTypes()->contains($type);
    }
}
