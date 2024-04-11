<?php

namespace App\Service\Connector\Lsvote\Model;

class LsvoteSitting
{
    private string $name;
    private string $date;
    private bool $remote;
    private bool $isMandatorAllowed;


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): LsvoteSitting
    {
        $this->name = $name;
        return $this;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): LsvoteSitting
    {
        $this->date = $date;
        return $this;
    }

    public function isRemote(): bool
    {
        return $this->remote;
    }

    public function setRemote(bool $remote): LsvoteSitting
    {
        $this->remote = $remote;

        return $this;
    }

    public function isMandatorAllowed(): bool
    {
        return $this->isMandatorAllowed;
    }

    public function setIsMandatorAllowed(bool $isMandatorAllowed): LsvoteSitting
    {
        $this->isMandatorAllowed = $isMandatorAllowed;
        return $this;
    }
}
