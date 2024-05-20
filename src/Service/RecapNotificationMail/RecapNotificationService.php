<?php

namespace App\Service\RecapNotificationMail;

class RecapNotificationService
{
    public function __construct(
        private readonly RecapDataProvider       $recapDataProvider,
        private readonly RecapFormater           $recapFormater,
        private readonly RecapNotificationMailer $recapNotificationMailer
    ) {
    }

    public function sendRecapNotifications(): void
    {
        $recapNotifications = $this->recapDataProvider->getAllStructuresRecapNotifications();
        $emailRecapData = $this->recapFormater->prepareRecapContentForAllUsers($recapNotifications);
        $this->recapNotificationMailer->sendAllNotifications($emailRecapData);
    }
}
