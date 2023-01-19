<?php

namespace App\Service\Convocation;

class ConvocationAttendance
{
    private string $attendance;
    private ?string $deputy;
    private ?bool $isRemote;
    private string $convocationId;

    public function getAttendance(): string
    {
        return $this->attendance;
    }

    public function setAttendance(string $attendance): ConvocationAttendance
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function getDeputy(): ?string
    {
        return $this->deputy;
    }

    public function setDeputy(?string $deputy): ConvocationAttendance
    {
        $this->deputy = $deputy;

        return $this;
    }

    public function getIsRemote(): ?bool
    {
        return $this->isRemote;
    }

    public function setIsRemote(?bool $isRemote): ConvocationAttendance
    {
        $this->isRemote = $isRemote;

        return $this;
    }

    public function getConvocationId(): string
    {
        return $this->convocationId;
    }

    public function setConvocationId(string $convocationId): ConvocationAttendance
    {
        $this->convocationId = $convocationId;

        return $this;
    }
}
