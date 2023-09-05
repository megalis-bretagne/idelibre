<?php

namespace App\Service\Convocation;

use App\Entity\User;

class ConvocationAttendance
{
    private string $convocationId;
    private ?string $attendance;
    public ?string $deputyId = null;
    public ?string $mandataire = null;

    public function getConvocationId(): string
    {
        return $this->convocationId;
    }

    public function setConvocationId(string $convocationId): ConvocationAttendance
    {
        $this->convocationId = $convocationId;

        return $this;
    }

    public function getAttendance(): string
    {
        return $this->attendance;
    }

    public function setAttendance(?string $attendance): ConvocationAttendance
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function getDeputyId(): ?string
    {
        return $this->deputyId;
    }

    public function setDeputyId(?string $deputyId): ConvocationAttendance
    {
        $this->deputyId = $deputyId;
        return $this;
    }

    public function getMandataire(): ?string
    {
        return $this->mandataire;
    }

    public function setMandataire(?string $mandataire): ConvocationAttendance
    {
        $this->mandataire = $mandataire;
        return $this;
    }

}
