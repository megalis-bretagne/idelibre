<?php


namespace App\Service\ApiEntity;


use Symfony\Component\Validator\Constraints as Assert;

class AnnexApi
{
    public ?string $linkedFile = null;
    /**
     * @Assert\NotBlank()
     */
    public ?int $rank;

    /**
     * @return string|null
     */
    public function getLinkedFile(): ?string
    {
        return $this->linkedFile;
    }

    /**
     * @param string|null $linkedFile
     * @return AnnexApi
     */
    public function setLinkedFile(?string $linkedFile): AnnexApi
    {
        $this->linkedFile = $linkedFile;
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
     * @return AnnexApi
     */
    public function setRank(?int $rank): AnnexApi
    {
        $this->rank = $rank;
        return $this;
    }




}
