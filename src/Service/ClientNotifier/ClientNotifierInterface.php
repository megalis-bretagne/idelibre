<?php

namespace App\Service\ClientNotifier;

interface ClientNotifierInterface
{
    public function newSittingNotification(string $userId);

    public function modifiedSittingNotification(string $userId);

    public function removedSittingNotification(string $userId);
}
