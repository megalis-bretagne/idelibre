<?php

namespace App\Service\ApiEntity;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProjectApi
{
    private ?string $id = null;
    #[NotBlank(message: 'Un projet doit avoir un nom')]
    #[Length(max: 500, maxMessage: 'Le nom du projet ne doit pas excéder 500 caractères')]
    private ?string $name;
    private ?string $themeId = null;
    private ?string $reporterId = null;
    private ?string $linkedFileKey = null;
    private ?string $fileName = null;

    #[NotBlank]
    private ?int $rank = null;
    private ?int $size = null;
    private ?string $path = null;

    /**
     * @var AnnexApi[]
     */
    private $annexes = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): ProjectApi
    {
        $this->name = $name;

        return $this;
    }

    public function getThemeId(): ?string
    {
        return $this->themeId;
    }

    public function setThemeId(?string $themeId): ProjectApi
    {
        $this->themeId = $themeId;

        return $this;
    }

    public function getReporterId(): ?string
    {
        return $this->reporterId;
    }

    public function setReporterId(?string $reporterId): ProjectApi
    {
        $this->reporterId = $reporterId;

        return $this;
    }

    public function getLinkedFileKey(): ?string
    {
        return $this->linkedFileKey;
    }

    public function setLinkedFileKey(?string $linkedFileKey): ProjectApi
    {
        $this->linkedFileKey = $linkedFileKey;

        return $this;
    }

    /**
     * @return AnnexApi[]
     */
    public function getAnnexes(): array
    {
        return $this->annexes;
    }

    /**
     * @param AnnexApi[] $annexes
     */
    public function setAnnexes(array $annexes): ProjectApi
    {
        $this->annexes = $annexes;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): ProjectApi
    {
        $this->rank = $rank;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): ProjectApi
    {
        $this->id = $id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): ProjectApi
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): ProjectApi
    {
        $this->size = $size;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): ProjectApi
    {
        $this->path = $path;

        return $this;
    }
}
