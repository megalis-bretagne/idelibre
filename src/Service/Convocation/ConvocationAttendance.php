<?php

namespace App\Service\Convocation;

use App\Entity\User;

class ConvocationAttendance
{
    private string $convocationId;
    private ?string $attendance;
    private ?string $replacement = null;
    public ?string $deputyId = null;


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

    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    public function setReplacement(?string $replacement): ConvocationAttendance
    {
        $this->replacement = $replacement;
        return $this;
    }

    public function getDeputy(): ?User
    {
//        dd($this->deputy);
        return $this->deputy;
    }

    /*public function setDeputy(?User $deputy): ConvocationAttendance
    {
        $this->deputy = $deputy;
        return $this;
    }*/
}
