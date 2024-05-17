<?php

namespace App\Service\NotificationMail;

use App\Entity\Convocation;
use App\Entity\Sitting;

class NotificationData
{
    /**
     * @param array<Convocation> $convocations
     */
    public function __construct(
        private readonly array   $convocations,
        private readonly Sitting $sitting,
        private readonly string  $timezone
    )
    {
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