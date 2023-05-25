<?php

namespace App\Service\Connector\Lsvote\Model;

class LsvoteSitting
{
    private string $name;

    private string $date;

    /**
     * @return mixed
     */

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

}