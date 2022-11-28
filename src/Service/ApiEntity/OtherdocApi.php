<?php

namespace App\Service\ApiEntity;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OtherdocApi
{
    private ?string $id = null;
    #[NotBlank(message: 'Un document doit avoir un nom')]
    #[Length(max: 'Le nom du document ne doit pas excéder 500 caractères')]
    private ?string $name;
    private ?string $linkedFileKey = null;
    private ?string $fileName = null;

    #[NotBlank]
    private ?int $rank = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): OtherdocApi
    {
        $this->name = $name;

        return $this;
    }

    public function getLinkedFileKey(): ?string
    {
        return $this->linkedFileKey;
    }

    public function setLinkedFileKey(?string $linkedFileKey): OtherdocApi
    {
        $this->linkedFileKey = $linkedFileKey;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): OtherdocApi
    {
        $this->rank = $rank;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): OtherdocApi
    {
        $this->id = $id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): OtherdocApi
    {
        $this->fileName = $fileName;

        return $this;
    }
}
