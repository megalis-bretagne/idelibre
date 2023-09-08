<?php

namespace App\Service\Connector\Lsvote\Model;

use App\Entity\User;

class LsvoteVoter
{
    private string $identifier;
    private string $firstName;
    private string $lastName;
    private bool $isDeputy = false;
    private ?LsvoteVoter $deputy;
    private ?string $mandatorId;


    public function getIdentifier(): string
    {
        return $this->identifier;
    }
    public function setIdentifier(string $identifier): LsvoteVoter
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function setFirstName(string $firstName): LsvoteVoter
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }
    public function setLastName(string $lastName): LsvoteVoter
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function isDeputy(): bool
    {
        return $this->isDeputy;
    }

    public function setIsDeputy(bool $isDeputy): LsvoteVoter
    {
        $this->isDeputy = $isDeputy;
        return $this;
    }

    public function getDeputy(): ?LsvoteVoter
    {
        return $this->deputy;
    }

    public function setDeputy(?LsvoteVoter $deputy): LsvoteVoter
    {
        $this->deputy = $deputy;
        return $this;
    }

    public function getMandatorId(): ?string
    {
        return $this->mandatorId;
    }

    public function setMandatorId(?string $mandatorId): LsvoteVoter
    {
        $this->mandatorId = $mandatorId;
        return $this;
    }

}
