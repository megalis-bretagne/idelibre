<?php

namespace App\Service\Connector\Lsvote\Model;

class LsvoteVoter
{
    private string $firstName;
    private string $lastName;
    private string $identifier;

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

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): LsvoteVoter
    {
        $this->identifier = $identifier;
        return $this;
    }
}
