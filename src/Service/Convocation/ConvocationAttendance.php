<?php

namespace App\Service\Convocation;

class ConvocationAttendance
{
    private string $attendance;
    private ?string $deputy;
    private string $convocationId;

    private string $replacement;

    public function getReplacement(): string
    {
        return $this->replacement;
    }

    public function setReplacement(string $replacement): ConvocationAttendance
    {
        $this->replacement = $replacement;
        return $this;
    }

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
