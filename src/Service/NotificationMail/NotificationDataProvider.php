<?php

namespace App\Service\NotificationMail;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use DateTime;

class NotificationDataProvider
{

    public function __construct(
        private readonly StructureRepository   $structureRepository,
        private readonly UserRepository        $userRepository,
        private readonly SittingRepository     $sittingRepository,
        private readonly ConvocationRepository $convocationRepository
    )
    {
    }


    /**
     * @return array<NotificationToSend>
     */
    public function getAllStructuresAttendanceNotifications(): array
    {
        $structures = $this->structureRepository->findBy(['isActive' => true]);

        $allStructuresNotifications = [];
        foreach ($structures as $structure) {
            $allStructuresNotifications = [...$allStructuresNotifications, ...$this->getStructureAttendanceNotifications($structure)];
        }

        return $allStructuresNotifications;
    }

    /**
     * @return array<NotificationToSend>
     */
    private function getStructureAttendanceNotifications(Structure $structure): array
    {
        $users = $this->userRepository->findSecretariesAndAdminByStructureWithMailsRecap($structure)->getQuery()->getResult();
        $sittings = $this->sittingRepository->findActiveSittingsAfterDateByStructure($structure, new DateTime('0 days'));

        $notificationsToSend = [];

        foreach ($users as $user) {
           $notificationsToSend[] = $this->getNotificationToSend($user, $sittings);
        }

        return $notificationsToSend;

    }

    /**
     * @param array<Sitting> $sittings
     */
    public function getNotificationToSend(User $user, array $sittings): NotificationToSend
    {
        $notificationData = [];
        foreach ($sittings as $sitting) {
            if ($this->isAuthorizedSittingType($sitting->getType(), $user)) {
                $notificationData[] = new NotificationData(
                    $this->convocationRepository->getConvocationsWithUserBySitting($sitting),
                    $sitting,
                    $sitting->getStructure()->getTimezone()->getName()
                );
            }
        }

        return new NotificationToSend($user, $notificationData, $user->getStructure());
    }

    private function isAuthorizedSittingType(Type $type, User $user): bool
    {
        return $user->getRole()->getName() === "Admin" or $user->getAuthorizedTypes()->contains($type);
    }

}