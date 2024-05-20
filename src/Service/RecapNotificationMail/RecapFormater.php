<?php

namespace App\Service\RecapNotificationMail;

use App\Entity\Convocation;
use App\Service\RecapNotificationMail\Model\EmailRecapData;
use App\Service\RecapNotificationMail\Model\RecapSittingInfo;
use Twig\Environment;

class RecapFormater
{
    public function __construct(private readonly Environment $twig)
    {
    }


    /**
     * @param array<EmailRecapData> $emailRecapData
     */
    public function prepareRecapContentForAllUsers(array $emailRecapData): array
    {
        foreach ($emailRecapData as $notificationToSend) {
            $content = [];
            foreach ($notificationToSend->getRecapSittingInfo() as $notificationData) {
                $content[] = $this->prepareRecapContent($notificationData);
            }
            $notificationToSend->setGeneratedRecapContent($content);
        }
        return $emailRecapData;
    }


    public function prepareRecapContent(RecapSittingInfo $recapSittingInfo): string
    {
        return $this->twig->render('generate/mailing_recap_template.html.twig', [
            'convocations' => $recapSittingInfo->getConvocations(),
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
            'sitting' => $recapSittingInfo->getSitting(),
            'timezone' => $recapSittingInfo->getTimezone(),
        ]);
    }
}
