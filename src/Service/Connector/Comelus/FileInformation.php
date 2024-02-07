<?php

namespace App\Service\Connector\Comelus;

class FileInformation
{

    public string $name;
    public string $rank;
    public string $linkedFile;



    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FileInformation
    {
        $this->name = $name;
        return $this;
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function setRank(string $rank): FileInformation
    {
        $this->rank = $rank;
        return $this;
    }

    public function getLinkedFile(): string
    {
        return $this->linkedFile;
    }

    public function setLinkedFile(string $linkedFile): FileInformation
    {
        $this->linkedFile = $linkedFile;
        return $this;
    }


}
