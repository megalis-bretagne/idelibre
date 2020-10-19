<?php


namespace App\Service\ApiEntity;


use Symfony\Component\Validator\Constraints as Assert;

class ProjectApi
{

    private ?string $id = null;
    /**
     * @Assert\NotBlank(message="Un projet doit avoir un nom")
     * @Assert\Length(max="Le nom du projet ne doit pas excéder 500 carractères")
     */
    private ?string $name;
    private ?string $themeId = null;
    private ?string $reporterId = null;
    private ?string $linkedFile = null;

    /**
     * @Assert\NotBlank()
     */
    private ?int $rank = null;


    /**
     * @var AnnexApi[]
     */
    private $annexes = [];

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ProjectApi
     */
    public function setName(?string $name): ProjectApi
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getThemeId(): ?string
    {
        return $this->themeId;
    }

    /**
     * @param string|null $themeId
     * @return ProjectApi
     */
    public function setThemeId(?string $themeId): ProjectApi
    {
        $this->themeId = $themeId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReporterId(): ?string
    {
        return $this->reporterId;
    }

    /**
     * @param string|null $reporterId
     * @return ProjectApi
     */
    public function setReporterId(?string $reporterId): ProjectApi
    {
        $this->reporterId = $reporterId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLinkedFile(): ?string
    {
        return $this->linkedFile;
    }

    /**
     * @param string|null $linkedFile
     * @return ProjectApi
     */
    public function setLinkedFile(?string $linkedFile): ProjectApi
    {
        $this->linkedFile = $linkedFile;
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
     * @return ProjectApi
     */
    public function setAnnexes(array $annexes): ProjectApi
    {
        $this->annexes = $annexes;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * @param int|null $rank
     * @return ProjectApi
     */
    public function setRank(?int $rank): ProjectApi
    {
        $this->rank = $rank;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ProjectApi
     */
    public function setId(?string $id): ProjectApi
    {
        $this->id = $id;
        return $this;
    }





}
