<?php

namespace App\Service\Connector\Lsvote\Model;

class LsvoteProject
{
    private string $name;
    private int $rank;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): LsvoteProject
    {
        $this->name = $name;
        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): LsvoteProject
    {
        $this->rank = $rank;
        return $this;
    }



}