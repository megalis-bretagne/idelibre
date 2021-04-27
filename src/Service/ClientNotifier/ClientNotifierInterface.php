<?php

namespace App\Service\ClientNotifier;

use App\Entity\Convocation;

interface ClientNotifierInterface
{
    /**
     * @param Convocation[] $convocations
     */
    public function newSittingNotification(array $convocations);

    /**
     * @param Convocation[] $convocations
     */
    public function modifiedSittingNotification(array $convocations);

    /**
     * @param Convocation[] $convocations
     */
    public function removedSittingNotification(array $convocations);
}
