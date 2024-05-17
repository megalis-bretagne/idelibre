<?php

namespace App\Service\NotificationMail;

use App\Entity\Convocation;
use Twig\Environment;

class NotificationFormater
{

    public function __construct(private readonly Environment $twig)
    {
    }


    /**
     * @param array<NotificationToSend> $notificationsToSend
     */
    public function prepareContentForAllUsers(array $notificationsToSend): array
    {
        $content = [];
        foreach ($notificationsToSend as $data) {
            $recapContent = ;
        }
        return $content;
    }


    public function prepareRecapContent(NotificationData $notificationData): string
    {
        return $this->twig->render('generate/mailing_recap_template.html.twig', [
            'convocations' => $notificationData->getConvocations(),
            'attendance' => [
                Convocation::PRESENT => 'Présent',
                Convocation::ABSENT => 'Absent',
                Convocation::REMOTE => 'Distanciel',
                Convocation::ABSENT_GIVE_POA => 'Donne pouvoir par procuration',
                Convocation::ABSENT_SEND_DEPUTY => 'Remplacé par son suppléant',
            ],
            'category' => [
                Convocation::CATEGORY_CONVOCATION => 'Élus',
                Convocation::CATEGORY_INVITATION => 'Invités/Personnels administratifs',
            ],
            'sitting' => $notificationData->getSitting(),
            'timezone' => $notificationData->getTimezone(),
        ]);
    }


}