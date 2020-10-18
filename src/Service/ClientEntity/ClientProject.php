<?php


namespace App\Service\ClientEntity;


use Symfony\Component\Validator\Constraints as Assert;

class ClientProject
{

    /**
     * @Assert\NotBlank(message="Un projet doit avoir un nom")
     * @Assert\Length(max="Le nom du projet ne doit pas excéder 500 carractères")
     */
    private ?string $name;
    private ?string $themeId = null;
    private ?string $rapporteurId = null;
    private ?string $linkedFile = null;
    /**
     * @Assert\NotBlank()
     */
    private ?int $rank = null;


    /**
     * @var ClientAnnex[]
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
     * @return ClientProject
     */
    public function setName(?string $name): ClientProject
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
     * @return ClientProject
     */
    public function setThemeId(?string $themeId): ClientProject
    {
        $this->themeId = $themeId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRapporteurId(): ?string
    {
        return $this->rapporteurId;
    }

    /**
     * @param string|null $rapporteurId
     * @return ClientProject
     */
    public function setRapporteurId(?string $rapporteurId): ClientProject
    {
        $this->rapporteurId = $rapporteurId;
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
     * @return ClientProject
     */
    public function setLinkedFile(?string $linkedFile): ClientProject
    {
        $this->linkedFile = $linkedFile;
        return $this;
    }

    /**
     * @return ClientAnnex[]
     */
    public function getAnnexes(): array
    {
        return $this->annexes;
    }

    /**
     * @param ClientAnnex[] $annexes
     * @return ClientProject
     */
    public function setAnnexes(array $annexes): ClientProject
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
     * @return ClientProject
     */
    public function setRank(?int $rank): ClientProject
    {
        $this->rank = $rank;
        return $this;
    }





}
