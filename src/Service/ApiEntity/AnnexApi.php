<?php

namespace App\Service\ApiEntity;

use Symfony\Component\Validator\Constraints\NotBlank;

class AnnexApi
{
    public ?string $id = null;

    public ?string $linkedFileKey = null;
    #[NotBlank]
    public ?int $rank = null;
    private ?int $size = null;

    public ?string $fileName = null;

    public function getLinkedFileKey(): ?string
    {
        return $this->linkedFileKey;
    }

    public function setLinkedFileKey(?string $linkedFileKey): AnnexApi
    {
        $this->linkedFileKey = $linkedFileKey;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): AnnexApi
    {
        $this->rank = $rank;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): AnnexApi
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): AnnexApi
    {
        $this->id = $id;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): AnnexApi
    {
        $this->size = $size;

        return $this;
    }
}
