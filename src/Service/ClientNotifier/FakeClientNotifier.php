<?php


namespace App\Service\ClientNotifier;


class FakeClientNotifier implements ClientNotifierInterface
{

    public function newSittingNotification(array $convocations)
    {
        // Fake newSittingNotification() method.
    }

    public function modifiedSittingNotification(array $convocations)
    {
        // Fake modifiedSittingNotification() method.
    }

    public function removedSittingNotification(array $convocations)
    {
        // Fake removedSittingNotification() method.
    }
}
