<?php

namespace App\Service\RecapNotificationMail\Model;

use App\Entity\Convocation;
use App\Entity\Sitting;

class RecapSittingInfo
{
    /**
     * @param array<Convocation> $convocations
     */
    public function __construct(
        private readonly array   $convocations,
        private readonly Sitting $sitting,
        private readonly string  $timezone
    ) {
    }

    public function getConvocations(): array
    {
        return $this->convocations;
    }

    public function getSitting(): Sitting
    {
        return $this->sitting;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }
}
